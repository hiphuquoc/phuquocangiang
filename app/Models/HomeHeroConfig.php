<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeHeroConfig extends Model
{
    protected $fillable = [
        'locale',
        'title',
        'title_accent',
        'tagline',
        'btn_primary_label',
        'btn_primary_url',
        'btn_primary_enabled',
        'btn_secondary_label',
        'btn_secondary_url',
        'btn_secondary_enabled',
        'is_active',
    ];

    protected $casts = [
        'btn_primary_enabled' => 'boolean',
        'btn_secondary_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function backgrounds(): HasMany
    {
        return $this->hasMany(HomeHeroBackground::class, 'hero_config_id')->orderBy('sort_order');
    }

    public function routeSlots(): HasMany
    {
        return $this->hasMany(HomeHeroRouteSlot::class, 'hero_config_id')->orderBy('sort_order');
    }

    public static function forLocale(string $locale): ?self
    {
        return self::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->with([
                'backgrounds' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                'routeSlots' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->first();
    }
}
