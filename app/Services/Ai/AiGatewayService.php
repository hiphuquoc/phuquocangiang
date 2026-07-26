<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AiGatewayService
{
    /**
     * Execute a chat completion request using OpenAI-compatible API.
     *
     * Options: model (string), debug (bool).
     *
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<string, mixed>
     */
    public function chat(array $messages, array $options = []): array
    {
        $this->assertEnabled();

        [$provider, $model] = $this->resolveProviderAndModel((string) ($options['model'] ?? ''));
        $profile = config('ai.providers.' . $provider, []);
        $baseUrl = rtrim((string) ($profile['base_url'] ?? ''), '/');
        $apiKey = (string) ($profile['api_key'] ?? '');
        $timeout = (int) config('ai.timeout', 90);

        if ($baseUrl === '' || $apiKey === '' || $model === '') {
            throw new RuntimeException('AI provider is not fully configured.');
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        $includeDebug = !empty($options['debug']);
        $endpoint = $baseUrl . '/chat/completions';

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post($endpoint, $payload);

        if (!$response->successful()) {
            $error = $response->json('error.message')
                ?? $response->body()
                ?? 'Unknown AI provider error';
            throw new RuntimeException('AI request failed: ' . Str::limit((string) $error, 500));
        }

        $json = $response->json();
        $content = data_get($json, 'choices.0.message.content');
        if (!is_string($content) || $content === '') {
            throw new RuntimeException('AI response does not contain content.');
        }

        $result = [
            'content' => $content,
            'model' => (string) data_get($json, 'model', $model),
            'provider' => $provider,
            'usage' => data_get($json, 'usage', []),
            'raw' => $json,
        ];

        if ($includeDebug) {
            $result['debug'] = [
                'provider' => $provider,
                'model' => $model,
                'endpoint' => $endpoint,
                'messages' => $messages,
                'payload' => $payload,
            ];
        }

        return $result;
    }

    /**
     * Lightweight helper for translation task.
     */
    public function translate(string $sourceText, string $targetLanguage, array $options = []): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional translator. Keep formatting, HTML, and placeholders unchanged.',
            ],
            [
                'role' => 'user',
                'content' => "Translate the following text to {$targetLanguage}. Return only translated text:\n\n" . $sourceText,
            ],
        ];

        return $this->chat($messages, $options);
    }

    /**
     * Lightweight helper for summarization task.
     */
    public function summarize(string $sourceText, array $options = []): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You summarize text clearly and concisely.',
            ],
            [
                'role' => 'user',
                'content' => "Summarize this content in bullet points:\n\n" . $sourceText,
            ],
        ];

        return $this->chat($messages, $options);
    }

    public function health(): array
    {
        $providers = (array) config('ai.providers', []);
        $providerHealth = [];
        foreach ($providers as $key => $profile) {
            $providerHealth[$key] = [
                'base_url' => (string) ($profile['base_url'] ?? ''),
                'has_api_key' => !empty($profile['api_key'] ?? ''),
                'default_model' => (string) ($profile['model'] ?? ''),
            ];
        }

        return [
            'enabled' => (bool) config('ai.enabled', false),
            'provider' => (string) config('ai.provider', ''),
            'timeout' => (int) config('ai.timeout', 90),
            'providers' => $providerHealth,
            'models' => (array) config('ai.models', []),
        ];
    }

    private function assertEnabled(): void
    {
        if (!config('ai.enabled', false)) {
            throw new RuntimeException('AI feature is disabled. Set AI_ENABLED=true to use this API.');
        }
    }

    /**
     * Resolve selected model string.
     * Accepted:
     * - "provider:model"
     * - "model" (uses default provider)
     *
     * @return array{0:string,1:string}
     */
    private function resolveProviderAndModel(string $selected): array
    {
        $selected = trim($selected);
        $defaultProvider = (string) config('ai.provider', 'openai');
        $providers = (array) config('ai.providers', []);

        if ($selected !== '' && str_contains($selected, ':')) {
            [$provider, $model] = array_pad(explode(':', $selected, 2), 2, '');
            $provider = trim($provider);
            $model = trim($model);
            if ($provider !== '' && $model !== '' && array_key_exists($provider, $providers)) {
                return [$provider, $model];
            }
        }

        if ($selected !== '' && !str_contains($selected, ':')) {
            return [$defaultProvider, $selected];
        }

        $defaultModel = (string) (config('ai.providers.' . $defaultProvider . '.model') ?? '');
        return [$defaultProvider, $defaultModel];
    }
}
