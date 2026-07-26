<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPromptTemplate extends Model
{
    protected $table = 'ai_prompt_templates';

    protected $fillable = [
        'name',
        'scope',
        'seo_type',
        'locale',
        'template_content',
        'part_before',
        'part_after',
        'default_model',
        'is_active',
        'is_default',
        'created_by',
    ];
}
