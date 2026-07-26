<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeHeroBackground extends Model
{
    protected $fillable = [
        'hero_config_id',
        'gcs_path',
        'public_url',
        'alt_text',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(HomeHeroConfig::class, 'hero_config_id');
    }

    public function mediaUrl(): string
    {
        if (!empty($this->public_url)) {
            return (string) $this->public_url;
        }

        if (!empty($this->gcs_path)) {
            return app(\App\Services\Media\GcsMediaStorageService::class)
                ->publicUrl((string) $this->gcs_path);
        }

        return route('media.heroBackground', ['background' => $this->id]);
    }
}
