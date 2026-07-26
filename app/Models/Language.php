<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Language — danh sách ngôn ngữ hệ thống hỗ trợ.
 *
 * Cách dùng phổ biến:
 *   Language::active()             // collection các ngôn ngữ đang public
 *   Language::default()            // ngôn ngữ mặc định
 *   Language::byCode('en')         // tìm theo code
 *   Language::all()->pluck('code') // ['vi', 'en', ...]
 */
class Language extends Model {
    use HasFactory;

    protected $table    = 'languages';
    protected $fillable = [
        'code', 'name', 'name_native', 'flag', 'og_locale',
        'dir', 'is_active', 'is_default', 'sort',
    ];
    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
        'sort'       => 'integer',
    ];

    /**
     * Cache dạng array (Laravel 13: serializable_classes=false không cho serialize Model).
     */
    private static function rememberRows(string $key, callable $query): array
    {
        return Cache::remember($key, 86400, function () use ($query) {
            $result = $query();

            if ($result instanceof Collection) {
                return $result->map->toArray()->all();
            }

            return $result ? $result->toArray() : [];
        });
    }

    private static function hydrate(?array $row): ?self
    {
        if (empty($row)) {
            return null;
        }

        return (new static)->forceFill($row)->syncOriginal();
    }

    /** @return Collection<int, self> */
    private static function hydrateMany(array $rows): Collection
    {
        return (new Collection($rows))->map(fn (array $row) => self::hydrate($row));
    }

    /** Cached collection các ngôn ngữ đang active (không hết hạn cho đến khi clear). */
    public static function active(): Collection
    {
        return self::hydrateMany(
            self::rememberRows('languages:active:v2', fn () => self::where('is_active', 1)->orderBy('sort')->get())
        );
    }

    /** Toàn bộ ngôn ngữ (cả active/inactive). */
    public static function listAll(): Collection
    {
        return self::hydrateMany(
            self::rememberRows('languages:all:v2', fn () => self::orderBy('sort')->get())
        );
    }

    /** Lấy ngôn ngữ mặc định (is_default = 1). */
    public static function default(): ?self
    {
        $row = self::rememberRows('languages:default:v2', function () {
            return self::where('is_default', 1)->first()
                ?? self::where('code', config('language.default_code', 'vi'))->first();
        });

        return self::hydrate($row ?: null);
    }

    public static function byCode(?string $code): ?self {
        if (empty($code)) return null;
        return self::active()->firstWhere('code', $code) ?? self::where('code', $code)->first();
    }

    public static function flushCache(): void {
        foreach (['languages:active', 'languages:all', 'languages:default', 'languages:active:v2', 'languages:all:v2', 'languages:default:v2'] as $key) {
            Cache::forget($key);
        }
    }

    public function translations() {
        return $this->hasMany(SeoTranslation::class, 'language_id', 'id');
    }

    /** Auto-flush cache khi save/delete */
    protected static function booted() {
        static::saved(fn() => self::flushCache());
        static::deleted(fn() => self::flushCache());
    }
}
