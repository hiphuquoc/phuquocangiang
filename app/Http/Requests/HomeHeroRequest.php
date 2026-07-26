<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => 'nullable|string|max:12',
            'title' => 'required|string|max:255',
            'title_accent' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:2000',
            'btn_primary_label' => 'nullable|string|max:120',
            'btn_primary_url' => 'nullable|string|max:500',
            'btn_secondary_label' => 'nullable|string|max:120',
            'btn_secondary_url' => 'nullable|string|max:500',
            'backgrounds_new.*' => 'nullable|image|max:8192',
            'routes' => 'nullable|array|max:4',
            'routes.*.ship_location_id' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề Hero không được để trống.',
            'backgrounds_new.*.image' => 'Ảnh nền phải là file hình ảnh hợp lệ.',
            'backgrounds_new.*.max' => 'Ảnh nền không được vượt quá 8MB.',
        ];
    }
}
