<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeIslandGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:12'],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'lead' => ['nullable', 'string', 'max:2000'],
            'meta_caption' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'photos_existing' => ['nullable', 'array'],
            'photos_existing.*' => ['integer'],
            'photos_alt' => ['nullable', 'array'],
            'photos_title' => ['nullable', 'array'],
            'photos_tag' => ['nullable', 'array'],
            'photos_pos' => ['nullable', 'array'],
            'photos_sort' => ['nullable', 'array'],
            'photos_new' => ['nullable', 'array'],
            'photos_new.*' => ['image', 'max:10240'],
            'photos_new_alt' => ['nullable', 'array'],
            'photos_new_alt.*' => ['nullable', 'string', 'max:500'],
        ];
    }
}

