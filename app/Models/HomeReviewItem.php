<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeReviewItem extends Model
{
    protected $fillable = [
        'reviews_config_id',
        'quote_text',
        'customer_name',
        'customer_meta',
        'tag',
        'rating',
        'avatar_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(HomeReviewsConfig::class, 'reviews_config_id');
    }

    public function avatarUrl(): string
    {
        $path = trim((string) ($this->avatar_path ?? ''));

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return (string) (media_variant_url($path, 'small') ?? media_url($path) ?? '');
    }
}
