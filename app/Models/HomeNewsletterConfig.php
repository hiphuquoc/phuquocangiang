<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeNewsletterConfig extends Model
{
    protected $fillable = [
        'locale',
        'stamp_text',
        'stamp_year',
        'kicker',
        'title',
        'lead',
        'field_label',
        'email_placeholder',
        'submit_text',
        'note',
        'sign_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
