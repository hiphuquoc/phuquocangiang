<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFaqItem extends Model
{
    protected $fillable = [
        'faq_config_id',
        'question',
        'answer_html',
        'sort_order',
        'is_open_default',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_open_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(HomeFaqConfig::class, 'faq_config_id');
    }
}
