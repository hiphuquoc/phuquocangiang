<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeReviewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:12'],
            'kicker' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'score_value' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'score_stat_value' => ['nullable', 'array', 'max:3'],
            'score_stat_value.*' => ['nullable', 'string', 'max:32'],
            'score_stat_label' => ['nullable', 'array', 'max:3'],
            'score_stat_label.*' => ['nullable', 'string', 'max:64'],
            'partners_label' => ['nullable', 'string', 'max:120'],
            'partners_text' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'reviews_existing' => ['nullable', 'array'],
            'reviews_existing.*' => ['integer'],
            'reviews_quote' => ['nullable', 'array'],
            'reviews_name' => ['nullable', 'array'],
            'reviews_meta' => ['nullable', 'array'],
            'reviews_tag' => ['nullable', 'array'],
            'reviews_rating' => ['nullable', 'array'],
            'reviews_sort' => ['nullable', 'array'],
            'reviews_avatar_url' => ['nullable', 'array'],
            'reviews_avatar' => ['nullable', 'array'],
            'reviews_avatar.*' => ['nullable', 'image', 'max:5120'],
            'reviews_new_quote' => ['nullable', 'array'],
            'reviews_new_name' => ['nullable', 'array'],
            'reviews_new_meta' => ['nullable', 'array'],
            'reviews_new_tag' => ['nullable', 'array'],
            'reviews_new_rating' => ['nullable', 'array'],
            'reviews_new_avatar_url' => ['nullable', 'array'],
            'reviews_new_avatar' => ['nullable', 'array'],
            'reviews_new_avatar.*' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
