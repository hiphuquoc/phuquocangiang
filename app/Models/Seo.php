<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Seo — bảng SEO trung tâm (1 row / entity).
 *
 * Phase 1 multilingual:
 *  - Các field translatable (title, description, seo_title, seo_description,
 *    slug, slug_full, link_canonical) đã được nhân bản qua `seo_translations`.
 *  - Để tương thích ngược, các cột gốc trên `seo` vẫn được duy trì và đồng bộ
 *    với bản dịch của ngôn ngữ DEFAULT (vi). Khi mọi controller chuyển sang
 *    dùng SeoTranslation hết -> sẽ DROP các cột này (Phase 2).
 *  - Magic accessors `$seo->title` ưu tiên trả về translation theo locale hiện
 *    tại; fallback về cột gốc.
 */
class Seo extends Model {
    use HasFactory;

    protected $table        = 'seo';
    protected $fillable     = [
        'title',
        'description',
        'image',
        'image_small',
        'level',
        'parent',
        'ordering',
        'topic',
        'seo_title',
        'seo_description',
        'slug',
        'slug_full',
        'link_canonical',
        'type',
        'rating_author_name',
        'rating_author_star',
        'rating_aggregate_count',
        'rating_aggregate_star',
        'video',
        'auto_post',
        'created_by',
        'created_at',
        'updated_at',
    ];

    /** Các cột localizable (cần lookup trong seo_translations) */
    public const LOCALIZABLE = [
        'title', 'description', 'seo_title', 'seo_description',
        'slug', 'slug_full', 'link_canonical',
    ];

    /* ============================================================
     *  Translation / multilingual API
     * ============================================================ */

    public function translations(): HasMany {
        return $this->hasMany(SeoTranslation::class, 'seo_id', 'id');
    }

    /** Một translation theo locale hiện tại (eager-loadable) */
    public function translation(?string $locale = null): HasOne {
        $locale ??= app()->getLocale();
        $langId = optional(Language::byCode($locale))->id;
        $rel = $this->hasOne(SeoTranslation::class, 'seo_id', 'id');
        if ($langId) $rel->where('language_id', $langId);
        return $rel;
    }

    /** Lấy translation row (1 query nếu chưa load) */
    public function translate(?string $locale = null): ?SeoTranslation {
        $locale ??= app()->getLocale();
        if ($this->relationLoaded('translations')) {
            $row = $this->translations->first(function ($t) use ($locale) {
                return optional($t->language)->code === $locale
                    || $t->language_id === optional(Language::byCode($locale))->id;
            });
            if ($row) return $row;
        }
        $langId = optional(Language::byCode($locale))->id;
        if (!$langId) return null;
        return SeoTranslation::where('seo_id', $this->id)
                             ->where('language_id', $langId)
                             ->first();
    }

    /**
     * Magic accessor: $seo->title, $seo->slug_full,...
     *
     * Quy tắc:
     *  - Nếu là field localizable -> ưu tiên seo_translations theo locale hiện tại
     *    -> fallback locale default -> fallback cột gốc trên `seo`.
     *  - Field còn lại trả như Eloquent bình thường.
     */
    public function getAttribute($key) {
        if (in_array($key, self::LOCALIZABLE, true)) {
            $row = $this->translate();
            if ($row && !empty($row->{$key})) return $row->{$key};

            // fallback default locale
            $defaultCode = config('language.default_code', 'vi');
            if (app()->getLocale() !== $defaultCode) {
                $rowDefault = $this->translate($defaultCode);
                if ($rowDefault && !empty($rowDefault->{$key})) return $rowDefault->{$key};
            }
        }
        return parent::getAttribute($key);
    }

    public function getImageAttribute($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return media_url($value) ?? $value;
    }

    public function getImageSmallAttribute($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return media_url($value) ?? $value;
    }

    /**
     * Trả về tất cả bản dịch khả dụng phục vụ hreflang.
     * @return \Illuminate\Support\Collection
     */
    public function alternates() {
        return $this->translations()->where('status', 'published')->get();
    }

    /* ============================================================
     *  CRUD legacy — vẫn giữ tương thích ngược
     * ============================================================ */

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new Seo();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id         = $model->id;

            // Tự đồng bộ translation default locale (nếu bảng tồn tại)
            if (Schema::hasTable('seo_translations')) {
                $defaultLang = Language::default();
                if ($defaultLang) {
                    SeoTranslation::firstOrCreate([
                        'seo_id'      => $id,
                        'language_id' => $defaultLang->id,
                    ], [
                        'title'           => $params['title'] ?? null,
                        'description'     => $params['description'] ?? null,
                        'seo_title'       => $params['seo_title'] ?? null,
                        'seo_description' => $params['seo_description'] ?? null,
                        'slug'            => $params['slug'] ?? null,
                        'slug_full'       => $params['slug_full'] ?? null,
                        'link_canonical'  => $params['link_canonical'] ?? null,
                        'status'          => 'published',
                    ]);
                }
            }
        }
        return $id;
    }

    public static function updateItem($id, $params){
        $flag = false;
        if (empty($id) || empty($params)) return $flag;

        $model = self::find($id);
        if (!$model) return $flag;

        $slugOld     = $model->getRawOriginal('slug');
        $slugFullOld = $model->getRawOriginal('slug_full');

        foreach ($params as $key => $value) $model->{$key} = $value;

        if (!array_key_exists('slug_full', $params)) {
            $model->slug_full = self::buildFullUrl($model->slug, $model->parent);
        }

        $flag = $model->save();
        if (!$flag) return $flag;

        $slugFullNew = $model->getRawOriginal('slug_full');

        if (!empty($slugFullOld) && $slugFullOld !== $slugFullNew) {
            self::createRedirect301($slugFullOld, $slugFullNew);
            self::renameContentBladeFiles($model->type, $slugOld, $model->slug);
            self::updateSlugFullChildrenRecursively($model->id);
        }

        // Sync sang seo_translations (default locale)
        if (Schema::hasTable('seo_translations')) {
            $defaultLang = Language::default();
            if ($defaultLang) {
                $payload = [];
                foreach (self::LOCALIZABLE as $col) {
                    if (array_key_exists($col, $params)) $payload[$col] = $params[$col];
                }
                if (!array_key_exists('slug_full', $payload) && !empty($model->slug_full)) {
                    $payload['slug_full'] = $model->getRawOriginal('slug_full');
                }
                if (!empty($payload)) {
                    SeoTranslation::updateOrCreate(
                        ['seo_id' => $id, 'language_id' => $defaultLang->id],
                        array_merge($payload, ['status' => 'published'])
                    );
                }
            }
        }

        return $flag;
    }

    public static function getItemBySlug($slug = null){
        $result = null;
        if(!empty($slug)){
            $result = self::select('*')
                        ->where('slug', $slug)
                        ->first();
        }
        return $result;
    }

    /**
     * Build slug_full cho ngôn ngữ default (legacy — vẫn dùng cho cột seo.slug_full).
     * Với multilingual: dùng SeoTranslation::buildFullUrl($slug, $parentSeoId, $languageId).
     */
    public static function buildFullUrl($slug, $levelOrParent = null, $parent = null){
        if (empty($slug)) return null;
        $parentId = $parent !== null ? $parent : $levelOrParent;
        if (empty($parentId)) return $slug;

        $parentSeo = self::select('slug_full')->where('id', $parentId)->first();
        if (!empty($parentSeo) && !empty($parentSeo->getRawOriginal('slug_full'))) {
            return rtrim($parentSeo->getRawOriginal('slug_full'), '/') . '/' . $slug;
        }
        return $slug;
    }

    public static function updateSlugFullChildrenRecursively($idParent){
        $childs = self::select('id', 'slug', 'slug_full', 'parent', 'type')
                        ->where('parent', $idParent)
                        ->get();

        foreach ($childs as $child) {
            $slugFullOld = $child->getRawOriginal('slug_full');
            $slugFullNew = self::buildFullUrl($child->getRawOriginal('slug'), $child->parent);
            if ($slugFullNew !== $slugFullOld) {
                self::where('id', $child->id)->update(['slug_full' => $slugFullNew]);
                // Đồng bộ translation default
                if (Schema::hasTable('seo_translations')) {
                    $defaultLang = Language::default();
                    if ($defaultLang) {
                        SeoTranslation::where('seo_id', $child->id)
                                      ->where('language_id', $defaultLang->id)
                                      ->update(['slug_full' => $slugFullNew]);
                    }
                }
                self::createRedirect301($slugFullOld, $slugFullNew);
            }
            $hasChild = self::where('parent', $child->id)->exists();
            if ($hasChild) self::updateSlugFullChildrenRecursively($child->id);
        }
    }

    public static function createRedirect301(?string $slugFullOld, ?string $slugFullNew): void {
        if (empty($slugFullOld) || empty($slugFullNew) || $slugFullOld === $slugFullNew) return;
        try {
            if (!Schema::hasTable('redirect_info')) return;

            $urlOld = '/' . ltrim($slugFullOld, '/');
            $urlNew = '/' . ltrim($slugFullNew, '/');

            $exists = DB::table('redirect_info')->where('url_old', $urlOld)->exists();
            if (!$exists) {
                DB::table('redirect_info')->insert(['url_old' => $urlOld, 'url_new' => $urlNew]);
            }
            DB::table('redirect_info')->where('url_new', $urlOld)->update(['url_new' => $urlNew]);
        } catch (\Throwable $e) {
            Log::warning('createRedirect301 failed: ' . $e->getMessage(), [
                'slug_old' => $slugFullOld,
                'slug_new' => $slugFullNew,
            ]);
        }
    }

    protected static function renameContentBladeFiles(?string $type, ?string $slugOld, ?string $slugNew): void {
        if (empty($type) || empty($slugOld) || empty($slugNew) || $slugOld === $slugNew) return;
        $cfg = config('tablemysql.' . $type);
        if (empty($cfg['content_dir'])) return;

        $dir = $cfg['content_dir'];
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $oldPath = $dir . $slugOld . '.blade.php';
        $newPath = $dir . $slugNew . '.blade.php';
        try {
            if ($disk->exists($oldPath) && !$disk->exists($newPath)) {
                $disk->move($oldPath, $newPath);
            }
        } catch (\Throwable $e) {
            Log::warning('renameContentBladeFiles failed: ' . $e->getMessage(), [
                'type' => $type, 'old' => $oldPath, 'new' => $newPath,
            ]);
        }
    }

    /* ============================================================
     *  Existing relations
     * ============================================================ */

    public function keywords() {
        return $this->hasMany(\App\Models\Keyword::class, 'seo_id', 'id');
    }

    public function contentspin() {
        return $this->hasOne(\App\Models\Contentspin::class, 'seo_id', 'id');
    }

    public function checkSeos() {
        return $this->hasMany(\App\Models\CheckSeo::class, 'seo_id', 'id');
    }
}
