<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Seo;
use App\Models\SeoTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * EntityTranslationService — service tổng quát cho admin để ghi/đọc nội dung đa ngôn ngữ.
 *
 * Nhiệm vụ:
 *  1. saveSeoTranslation()    — upsert 1 dòng seo_translations cho ($seoId, $locale).
 *  2. saveEntityTranslation() — upsert 1 dòng <entity>_translations cho ($entity, $locale).
 *  3. saveAll()               — gọi cả 2 hàm trên trong 1 transaction; dùng trong AdminController::store/update.
 *
 * Quy ước data:
 *  - $seoTranslationData: ['title','description','seo_title','seo_description','slug','link_canonical','status']
 *  - $entityTranslationData: chỉ cần các field nằm trong $model::$translatableFields
 */
class EntityTranslationService {
    /**
     * Upsert seo_translations cho ($seoId, $locale).
     * - Tự build slug_full nếu thiếu (dựa trên parent locale slug).
     * - Tự tạo redirect 301 + cascade slug_full khi đổi slug (qua SeoTranslation::upsertTranslation).
     */
    public static function saveSeoTranslation(int $seoId, string $locale, array $data): ?SeoTranslation {
        if (empty($seoId) || empty($locale)) return null;

        $seo = Seo::find($seoId);
        if (!$seo) return null;

        $language = Language::byCode($locale);
        if (!$language) return null;

        $payload = [
            'title'            => $data['title']            ?? null,
            'description'      => $data['description']      ?? null,
            'seo_title'        => $data['seo_title']        ?? ($data['title'] ?? null),
            'seo_description'  => $data['seo_description']  ?? ($data['description'] ?? null),
            'slug'             => $data['slug']             ?? null,
            'link_canonical'   => $data['link_canonical']   ?? null,
            'status'           => $data['status']           ?? 'published',
            'translated_by'    => Auth::id() ?? 0,
        ];
        // Đẩy logic build slug_full + 301 redirect cascade vào SeoTranslation::upsertTranslation
        return SeoTranslation::upsertTranslation($seo->id, $language->id, $payload);
    }

    /**
     * Upsert <entity>_translations cho ($entity, $locale).
     * Bắt buộc model dùng trait HasTranslations (có $translationModel + $translatableFields).
     */
    public static function saveEntityTranslation(Model $entity, string $locale, array $data): ?Model {
        if (empty($locale) || empty($entity)) return null;

        // Chỉ giữ những field thực sự nằm trong $translatableFields để tránh ghi nhầm cột không tồn tại
        $allowed = property_exists($entity, 'translatableFields') && is_array($entity->translatableFields)
            ? $entity->translatableFields : [];
        $filtered = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $filtered[$field] = $data[$field];
            }
        }
        if (empty($filtered)) {
            // Nếu form không gửi field nào, bỏ qua (admin có thể chỉ update SEO)
            return null;
        }
        $filtered['status']        = $data['status']        ?? 'published';
        $filtered['translated_by'] = Auth::id() ?? 0;

        // upsertTranslation đã được trait HasTranslations cung cấp
        return $entity->upsertTranslation($locale, $filtered);
    }

    /**
     * Tiện ích: gọi đồng thời SEO + entity trong 1 transaction.
     * @param  Seo|null     $seo               (có thể null nếu chỉ update entity translation)
     * @param  Model|null   $entity            (có thể null nếu chỉ update seo translation)
     * @param  string       $locale            mã ngôn ngữ ('vi','en','zh',...)
     * @param  array        $seoData           dữ liệu cho seo_translations
     * @param  array        $entityData        dữ liệu cho <entity>_translations
     */
    public static function saveAll(?Seo $seo, ?Model $entity, string $locale, array $seoData = [], array $entityData = []): array {
        return DB::transaction(function () use ($seo, $entity, $locale, $seoData, $entityData) {
            $seoTranslation    = $seo    ? self::saveSeoTranslation($seo->id, $locale, $seoData) : null;
            $entityTranslation = $entity ? self::saveEntityTranslation($entity, $locale, $entityData) : null;
            return [
                'seo_translation'    => $seoTranslation,
                'entity_translation' => $entityTranslation,
            ];
        });
    }

    /**
     * Tiện ích đọc trực tiếp từ Request (admin form gửi:
     *   translations[<code>][seo][...]
     *   translations[<code>][entity][...]
     * ).
     * Tự động đảm bảo:
     *  - locale mặc định luôn có 1 row seo_translations + entity_translation.
     *  - nếu form không gửi cho 1 locale thì giữ nguyên.
     *  - khi form rỗng cho locale (không có ô nhập) -> nếu là default locale, fallback dùng dữ liệu form chính.
     *
     * @param Seo|null $seo            Bản ghi seo vừa được lưu
     * @param Model|null $entity       Bản ghi entity vừa được lưu (đã có id)
     * @param array $translationsInput $request->input('translations', [])
     * @param array $defaultLocaleSeo  Map ngầm định (form chính) cho default locale - dùng khi form không có tab default
     * @param array $defaultLocaleEntity
     * @return array map: [locale => ['seo_translation' => ..., 'entity_translation' => ...]]
     */
    public static function persistFromRequest(?Seo $seo, ?Model $entity, array $translationsInput, array $defaultLocaleSeo = [], array $defaultLocaleEntity = []): array {
        $defaultCode = config('language.default_code', 'vi');
        $result = [];

        // Đảm bảo default locale luôn được persist (kể cả form không chứa block default)
        if (!isset($translationsInput[$defaultCode])) {
            $translationsInput[$defaultCode] = ['seo' => [], 'entity' => []];
        }

        foreach ($translationsInput as $code => $blocks) {
            $seoData    = $blocks['seo']    ?? [];
            $entityData = $blocks['entity'] ?? [];

            // Default locale: merge với data form chính nếu trống
            if ($code === $defaultCode) {
                foreach ($defaultLocaleSeo as $k => $v) {
                    if (!isset($seoData[$k]) || $seoData[$k] === null || $seoData[$k] === '') $seoData[$k] = $v;
                }
                foreach ($defaultLocaleEntity as $k => $v) {
                    if (!isset($entityData[$k]) || $entityData[$k] === null || $entityData[$k] === '') $entityData[$k] = $v;
                }
            }

            // Nếu cả 2 block đều rỗng (locale không nhập gì) -> bỏ qua
            $hasSeo    = !empty(array_filter($seoData,    fn($v) => $v !== null && $v !== ''));
            $hasEntity = !empty(array_filter($entityData, fn($v) => $v !== null && $v !== ''));
            if (!$hasSeo && !$hasEntity) continue;

            $result[$code] = self::saveAll(
                $hasSeo    ? $seo    : null,
                $hasEntity ? $entity : null,
                $code,
                $seoData,
                $entityData
            );
        }
        return $result;
    }

    /**
     * Lấy collection seo_translations + entity_translations cho 1 entity, dạng map [locale => row]
     * Phục vụ render form admin tab đa ngôn ngữ.
     */
    public static function loadAllTranslations(?Seo $seo, ?Model $entity): array {
        $result = ['seo' => [], 'entity' => []];

        if ($seo && $seo->exists) {
            $seo->loadMissing(['translations.language']);
            foreach ($seo->translations as $tr) {
                $code = $tr->language->code ?? null;
                if ($code) $result['seo'][$code] = $tr;
            }
        }
        if ($entity && method_exists($entity, 'translations')) {
            $entity->loadMissing(['translations.language']);
            foreach ($entity->translations as $tr) {
                $code = $tr->language->code ?? null;
                if ($code) $result['entity'][$code] = $tr;
            }
        }
        return $result;
    }
}
