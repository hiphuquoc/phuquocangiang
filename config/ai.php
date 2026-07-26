<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Feature Toggle
    |--------------------------------------------------------------------------
    */
    'enabled' => env('AI_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    | Current implementation uses OpenAI-compatible chat completions API.
    | This lets you switch between OpenAI / DeepInfra / other compatible
    | providers by just changing endpoint + key.
    */
    'provider' => env('AI_PROVIDER', 'openai_compatible'),

    /*
    |--------------------------------------------------------------------------
    | Auth For Internal API
    |--------------------------------------------------------------------------
    | Optional static key for automation jobs (cron, workers, external tools).
    | Send with header: X-AI-API-KEY: <key>
    */
    'api_key' => env('AI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Provider Profiles (OpenAI-compatible APIs)
    |--------------------------------------------------------------------------
    | All providers below are called through chat/completions-compatible format.
    | Add keys in .env and select model by "provider:model" format.
    */
    'providers' => [
        'openai' => [
            'base_url' => env('AI_OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('AI_OPENAI_API_KEY'),
            'model' => env('AI_OPENAI_MODEL', 'gpt-5.4-mini'),
        ],
        'deepinfra' => [
            'base_url' => env('AI_DEEPINFRA_BASE_URL', 'https://api.deepinfra.com/v1/openai'),
            'api_key' => env('AI_DEEPINFRA_API_KEY', env('DEEP_INFRA_API_KEY')),
            'model' => env('AI_DEEPINFRA_MODEL', 'meta-llama/Meta-Llama-3.1-70B-Instruct'),
        ],
        'deepseek' => [
            'base_url' => env('AI_DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
            'api_key' => env('AI_DEEPSEEK_API_KEY', env('DEEP_SEEK_API_KEY')),
            'model' => env('AI_DEEPSEEK_MODEL', 'deepseek-chat'),
        ],
        'qwen' => [
            'base_url' => env('AI_QWEN_BASE_URL', 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'),
            'api_key' => env('AI_QWEN_API_KEY', env('QWEN_API_KEY')),
            'model' => env('AI_QWEN_MODEL', 'qwen-plus'),
        ],
        'xai' => [
            'base_url' => env('AI_XAI_BASE_URL', 'https://api.x.ai/v1'),
            'api_key' => env('AI_XAI_API_KEY', env('GROK_API_KEY')),
            'model' => env('AI_XAI_MODEL', 'grok-2-latest'),
        ],
        'anthropic' => [
            // Keep OpenAI-compatible gateway endpoint if you use one.
            'base_url' => env('AI_ANTHROPIC_BASE_URL', ''),
            'api_key' => env('AI_ANTHROPIC_API_KEY', env('CLAUDE_AI_API_KEY')),
            'model' => env('AI_ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        ],
    ],

    'timeout' => (int) env('AI_TIMEOUT_SECONDS', 90),

    /*
    |--------------------------------------------------------------------------
    | Debug (translation admin: gửi debug=1 để nhận prompt trong JSON → console)
    |--------------------------------------------------------------------------
    */
    'debug' => (bool) env('AI_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Model Catalog Shown In UI
    |--------------------------------------------------------------------------
    | Format: provider:model
    */
    'models' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_AVAILABLE_MODELS', 'openai:gpt-5.4-mini,openai:gpt-5.4,openai:gpt-5.5,openai:gpt-4o-mini'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Default translation prompt (khi không chọn / không lưu template DB)
    | Tokens: [source] [target_language] [locale] [seo_type]
    |--------------------------------------------------------------------------
    */
    'translation_prompt_default' => <<<'PROMPT'
Dịch nội dung sau sang ngôn ngữ [target_language]
Yêu cầu:
- Chuẩn văn phong, ngôn ngữ địa phương, thông dụng
- Dùng cho website, chuẩn SEO và dễ hiểu
Nội dung cần dịch:
"[source]"
PROMPT,
];
