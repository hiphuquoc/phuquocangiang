<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeIslandGalleryPhoto extends Model
{
    protected $fillable = [
        'gallery_config_id',
        'gcs_path',
        'alt_text',
        'title',
        'tag',
        'object_position',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(HomeIslandGalleryConfig::class, 'gallery_config_id');
    }

    public function thumbUrl(): string
    {
        return (string) (media_variant_url($this->gcs_path, 'small') ?? media_url($this->gcs_path) ?? '');
    }

    public function displayUrl(): string
    {
        return (string) (media_variant_url($this->gcs_path, 'medium') ?? media_url($this->gcs_path) ?? '');
    }

    public function lightboxUrl(): string
    {
        return (string) (media_variant_url($this->gcs_path, 'original') ?? media_url($this->gcs_path) ?? '');
    }
}

