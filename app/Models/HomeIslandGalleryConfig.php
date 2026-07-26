<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeIslandGalleryConfig extends Model
{
    protected $fillable = [
        'locale',
        'eyebrow',
        'title',
        'lead',
        'meta_caption',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(HomeIslandGalleryPhoto::class, 'gallery_config_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function activePhotos(): HasMany
    {
        return $this->photos()->where('is_active', true);
    }
}

