<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeFaqRequest extends FormRequest
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
            'help_title' => ['nullable', 'string', 'max:255'],
            'help_body' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'faqs_existing' => ['nullable', 'array'],
            'faqs_existing.*' => ['integer'],
            'faqs_question' => ['nullable', 'array'],
            'faqs_answer' => ['nullable', 'array'],
            'faqs_sort' => ['nullable', 'array'],
            'faqs_open' => ['nullable', 'array'],
            'faqs_new_question' => ['nullable', 'array'],
            'faqs_new_answer' => ['nullable', 'array'],
            'faqs_new_open' => ['nullable', 'array'],
        ];
    }
}
