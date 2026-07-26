<?php

namespace App\Models\Concerns;

use App\Models\Language;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait HasTranslations.
 *
 * Áp dụng cho mọi entity có bảng dịch tương ứng `<table>_translations`.
 * Yêu cầu model khai báo:
 *   - public string $translationModel    (FQCN của model dịch, ví dụ TourTranslation::class)
 *   - public array  $translatableFields  (mảng các cột được phép dịch — đồng bộ với config/tablemysql.php)
 *
 * API:
 *   $tour->translations()                // HasMany toàn bộ bản dịch
 *   $tour->translation('en')             // HasOne translation theo locale (eager-loadable)
 *   $tour->translate('en')               // Eloquent record (1 query nếu chưa load)
 *   $tour->trans('name', 'en')           // Lấy giá trị 1 trường, fallback về cột gốc nếu rỗng
 *   $tour->upsertTranslation($lang, $params)
 *
 * Convention:
 *   - Tên bảng dịch = $entityTable.'_translations'
 *   - Tên cột FK    = $entityTable.'_id'
 *
 * Sample sử dụng trong Blade:
 *   {{ $tour->trans('name') }}                  // theo locale hiện tại (app()->getLocale())
 *   {{ $tour->trans('description', 'en') }}     // ép locale
 */
trait HasTranslations
{
    /**
     * Cache 1-record-per-(model, locale) cho translate() trong cùng request.
     * Tránh n+1 query khi getAttribute() dùng translation.
     */
    protected array $__transCache = [];

    /**
     * V3.0: Magic locale-aware accessor.
     *
     * Nếu locale hiện tại ≠ default + field nằm trong $translatableFields:
     *   - tra translation row
     *   - nếu có giá trị non-empty → return giá trị đó
     *   - nếu rỗng → fallback giá trị cột gốc (preserve UX cho field chưa dịch)
     *
     * Cách này cho phép view (cả admin và frontend) dùng `$tour->name` mà tự
     * động lấy bản dịch khi đang ở locale khác — KHÔNG cần thay đổi view.
     *
     * Quan trọng: chỉ kích hoạt khi:
     *  1. Field nằm trong $translatableFields (an toàn cho master columns).
     *  2. Locale hiện tại khác locale mặc định.
     *  3. Không phải đang trong context "raw" (e.g. saving Eloquent itself,
     *     dirty tracking — Laravel gọi getAttribute nội bộ rất nhiều).
     *
     * Để tắt override (ví dụ admin muốn xem giá trị raw):
     *    app()->bind('translation.bypass', fn() => true);
     */
    public function getAttribute($key)
    {
        $rawValue = parent::getAttribute($key);

        if (!$this->__shouldOverrideAttribute($key)) {
            return $rawValue;
        }

        $locale = app()->getLocale();
        $row = $this->__cachedTranslation($locale);
        if ($row && isset($row->{$key}) && $row->{$key} !== '' && $row->{$key} !== null) {
            return $row->{$key};
        }
        return $rawValue;
    }

    private function __shouldOverrideAttribute(string $key): bool
    {
        // Bypass khi explicitly không muốn locale-aware (ví dụ tool migration)
        if (app()->bound('translation.bypass') && app('translation.bypass')) return false;

        // Chỉ override field translatable
        if (!property_exists($this, 'translatableFields') || !is_array($this->translatableFields)) return false;
        if (!in_array($key, $this->translatableFields, true)) return false;

        // Chỉ override khi locale ≠ default
        $locale = app()->getLocale();
        $default = config('language.default_code', 'vi');
        if ($locale === $default) return false;

        // Object phải đã saved (có id) — translation không thể lookup khi chưa có FK
        if (!$this->exists || !$this->getKey()) return false;

        return true;
    }

    private function __cachedTranslation(string $locale)
    {
        if (array_key_exists($locale, $this->__transCache)) return $this->__transCache[$locale];
        try {
            $row = $this->translate($locale);
        } catch (\Throwable $e) {
            $row = null;
        }
        return $this->__transCache[$locale] = $row;
    }

    /**
     * Override trong model con nếu cần FQCN custom.
     * Mặc định: namespace App\Models, class = Studly($entityTable).'Translation'.
     */
    public function getTranslationModelClass(): string
    {
        if (property_exists($this, 'translationModel') && !empty($this->translationModel)) {
            return $this->translationModel;
        }
        $base = class_basename(static::class);
        $guess = "App\\Models\\{$base}Translation";
        return class_exists($guess) ? $guess : $guess;
    }

    public function getTranslatableForeignKey(): string
    {
        return $this->getTable() . '_id';
    }

    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModelClass(), $this->getTranslatableForeignKey(), 'id');
    }

    /** Eager-loadable: $tour->load(['translation' => fn($q) => $q->whereHas('language',...) ]) */
    public function translation(?string $locale = null): HasOne
    {
        $locale ??= app()->getLocale();
        $langId = optional(Language::byCode($locale))->id;

        $relation = $this->hasOne($this->getTranslationModelClass(), $this->getTranslatableForeignKey(), 'id');
        if ($langId) $relation->where('language_id', $langId);
        return $relation;
    }

    /** Lấy ra Eloquent record (1 query nếu không có sẵn). */
    public function translate(?string $locale = null)
    {
        $locale ??= app()->getLocale();
        // Nếu translations đã được eager load -> tìm trong collection (0 query)
        if ($this->relationLoaded('translations')) {
            $row = $this->translations->first(function ($t) use ($locale) {
                $code = optional($t->language)->code;
                return $code === $locale || $t->language_id === optional(Language::byCode($locale))->id;
            });
            if ($row) return $row;
        }

        $langId = optional(Language::byCode($locale))->id;
        if (!$langId) return null;

        $modelClass = $this->getTranslationModelClass();
        return $modelClass::where($this->getTranslatableForeignKey(), $this->id)
                        ->where('language_id', $langId)
                        ->first();
    }

    /**
     * Lấy giá trị field theo locale, fallback chuỗi:
     *  1. translation locale hiện tại
     *  2. translation default locale
     *  3. cột gốc trên entity (cho dual-read trong giai đoạn migrate)
     *
     * @return mixed|null
     */
    public function trans(string $field, ?string $locale = null)
    {
        $locale ??= app()->getLocale();

        $row = $this->translate($locale);
        if ($row && !empty($row->{$field})) return $row->{$field};

        // Fallback default locale
        $defaultCode = config('language.default_code', 'vi');
        if ($locale !== $defaultCode) {
            $rowDefault = $this->translate($defaultCode);
            if ($rowDefault && !empty($rowDefault->{$field})) return $rowDefault->{$field};
        }

        // Fallback cột gốc trên entity
        if (array_key_exists($field, $this->attributes ?? [])) {
            return $this->attributes[$field];
        }
        return null;
    }

    /**
     * Insert / update bản dịch.
     * @param string|int $localeOrId  code 'en' hoặc id của languages
     * @param array      $params      mảng cột dịch
     */
    public function upsertTranslation($localeOrId, array $params)
    {
        $langId = is_numeric($localeOrId) ? (int) $localeOrId : optional(Language::byCode($localeOrId))->id;
        if (!$langId) return null;

        $modelClass = $this->getTranslationModelClass();
        $fk = $this->getTranslatableForeignKey();

        $row = $modelClass::firstOrNew([
            $fk           => $this->id,
            'language_id' => $langId,
        ]);

        // Chỉ ghi các field nằm trong translatableFields (an toàn)
        $allowed = property_exists($this, 'translatableFields') ? $this->translatableFields : array_keys($params);
        foreach ($params as $k => $v) {
            if (!in_array($k, $allowed, true) && !in_array($k, ['status', 'translated_by'], true)) continue;
            $row->{$k} = $v;
        }
        $row->save();
        return $row;
    }
}
