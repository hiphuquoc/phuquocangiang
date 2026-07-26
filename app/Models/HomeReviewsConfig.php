<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeReviewsConfig extends Model
{
    protected $fillable = [
        'locale',
        'kicker',
        'title',
        'description',
        'score_value',
        'score_stats',
        'partners_label',
        'partners',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'score_value' => 'float',
        'score_stats' => 'array',
        'partners' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(HomeReviewItem::class, 'reviews_config_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }
}
