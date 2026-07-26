<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeHeroRouteSlot extends Model
{
    protected $fillable = [
        'hero_config_id',
        'ship_location_id',
        'ship_id',
        'label_from',
        'label_to',
        'duration_label',
        'price_label',
        'link_url',
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

    public function shipLocation(): BelongsTo
    {
        return $this->belongsTo(ShipLocation::class, 'ship_location_id');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class, 'ship_id');
    }
}
