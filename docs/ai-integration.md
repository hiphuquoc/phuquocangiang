# AI Integration Guide

This document describes the new AI API foundation in `hitour.dev` for automation use-cases.

## 1) Scope

Implemented components:

- `config/ai.php`: central AI settings
- `app/Services/Ai/AiGatewayService.php`: provider-agnostic AI gateway (OpenAI-compatible endpoint)
- `app/Http/Controllers/Api/AiController.php`: API controller for AI features
- `app/Http/Middleware/EnsureAiApiKey.php`: optional API key protection for automation
- `routes/api.php`: AI routes under `/api/ai/*`

## 2) Environment Configuration

Add these variables to your environment:

```env
AI_ENABLED=true
AI_PROVIDER=openai
AI_API_KEY=your_internal_ai_api_key

# Format: provider:model
AI_AVAILABLE_MODELS=openai:gpt-4o-mini,openai:gpt-4o,openai:gpt-4.1-mini,openai:gpt-4.1,deepseek:deepseek-chat,deepseek:deepseek-reasoner,qwen:qwen-turbo,qwen:qwen-plus,qwen:qwen-max,xai:grok-2-latest,deepinfra:meta-llama/Meta-Llama-3.1-70B-Instruct,anthropic:claude-3-5-sonnet-latest
AI_TIMEOUT_SECONDS=90

AI_OPENAI_BASE_URL=https://api.openai.com/v1
AI_OPENAI_API_KEY=your_provider_secret_key
AI_OPENAI_MODEL=gpt-4o-mini

AI_DEEPSEEK_BASE_URL=https://api.deepseek.com/v1
AI_DEEPSEEK_API_KEY=your_deepseek_key
AI_DEEPSEEK_MODEL=deepseek-chat

AI_QWEN_BASE_URL=https://dashscope-intl.aliyuncs.com/compatible-mode/v1
AI_QWEN_API_KEY=your_qwen_key
AI_QWEN_MODEL=qwen-plus

AI_XAI_BASE_URL=https://api.x.ai/v1
AI_XAI_API_KEY=your_xai_key
AI_XAI_MODEL=grok-2-latest

AI_DEEPINFRA_BASE_URL=https://api.deepinfra.com/v1/openai
AI_DEEPINFRA_API_KEY=your_deepinfra_key
AI_DEEPINFRA_MODEL=meta-llama/Meta-Llama-3.1-70B-Instruct

# Optional (requires OpenAI-compatible gateway for Anthropic in current implementation)
AI_ANTHROPIC_BASE_URL=
AI_ANTHROPIC_API_KEY=your_anthropic_key
AI_ANTHROPIC_MODEL=claude-3-5-sonnet-latest
```

Notes:

- `AI_API_KEY` is for your own automation clients (`X-AI-API-KEY` header), not provider authentication.
- If `AI_API_KEY` is empty, middleware allows requests (useful for local setup only).
- UI model selector uses `AI_AVAILABLE_MODELS` (supports `provider:model`).
- Current gateway expects OpenAI-compatible Chat Completions APIs.

## 3) Available Endpoints

Base prefix: `/api/ai`  
Security: `throttle:api` + `ai.key` middleware.

### `GET /api/ai/health`

Returns feature/config status:

- enabled
- provider
- base_url
- has_api_key
- default_model
- timeout

### `POST /api/ai/chat`

Request body:

```json
{
  "messages": [
    { "role": "system", "content": "You are helpful." },
    { "role": "user", "content": "Write short itinerary for Phu Quoc." }
  ],
  "model": "gpt-4o-mini",
  "temperature": 0.3,
  "max_tokens": 1200
}
```

### `POST /api/ai/translate`

Request body:

```json
{
  "text": "Xin chao Phu Quoc",
  "target_language": "English",
  "model": "gpt-4o-mini"
}
```

### `POST /api/ai/summarize`

Request body:

```json
{
  "text": "Long source content here..."
}
```

## 4) Response Contract

Success format:

```json
{
  "success": true,
  "data": {}
}
```

Validation error:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {}
}
```

Provider/runtime error:

```json
{
  "success": false,
  "message": "AI request failed: ..."
}
```

## 5) Design Decisions (Compared to legacy ChatGPTController style)

- Separated responsibilities by layers (controller vs gateway service).
- Avoided hardcoding model/provider logic in controller.
- Standardized JSON response format for automation consumers.
- Added request validation for safer API behavior.
- Added optional internal API key middleware for service-to-service calls.
- Kept OpenAI-compatible abstraction to switch providers quickly.

## 6) Next Recommended Steps

- Add prompt templates table + prompt versioning in DB.
- Add async queue jobs for long tasks (content generation pipelines).
- Add request/response logging (token usage, latency, error rate).
- Add policy layer by feature (`translate`, `summary`, `seo-generate`).
- Add provider failover strategy when primary provider is down.

## 7) Multilingual AI Autofill (Admin Translation Mode)

Integrated feature for translation pages (`/he-thong/translation/{locale}/{seoId}`):

- A dedicated **AI Translation panel** is rendered in the right sidebar, below save actions.
- User can choose a model (`AI_AVAILABLE_MODELS`) and click **"Dịch toàn bộ"**.
- System collects source data from default language and sends to AI.
- AI response auto-fills all translatable inputs, relation repeaters, schedule, and body content.
- Editor still reviews and manually clicks **Save** (human-in-the-loop flow).

### Backend Endpoint

- `POST /he-thong/translation/{locale}/{seoId}/ai-draft`
- Route name: `admin.translation.aiDraft`
- Auth: same admin guard as other translation routes.

### Source Data Strategy

The draft endpoint builds translation source payload from default locale:

- SEO fields: `title`, `description`, `seo_title`, `seo_description`, `slug`, `link_canonical`
- Entity translatable fields from `config/tablemysql.php`
- Relation fields via `translation_relations` (`top_level` and `array`)
- Legacy `schedule` for `ship_info` / `ship_location` from default file
- `seo_content_translations` default content for body translation

### Frontend Autofill Strategy

`public/js/admin/translation-mode.js` applies AI output directly to:

- Top-level inputs by exact `name`
- Repeater rows by hidden id alias (e.g. `question_answer_info_id`)
- `body_content_translation` textarea

After autofill, fields are marked as translated and remain editable before final save.

## 8) Prompt Template Customization (Database-backed)

To improve translation quality and avoid retyping prompts every time, prompt templates are now persisted in DB.

### Database

- Table: `ai_prompt_templates`
- Migration: `2026_05_05_220000_create_ai_prompt_templates_table.php`
- Model: `App\Models\AiPromptTemplate`

Core columns:

- `name`: template name for quick reuse
- `scope`: current use `translation`
- `seo_type`: optional filter by page type (e.g. `tour_info`, `ship_location`)
- `locale`: optional filter by target locale
- `template_content`: dynamic prompt template text (token-based)
- `part_before` / `part_after`: legacy compatibility fields
- `default_model`: preferred model for that template
- `is_default`: default template in current scope
- `is_active`: soft disable

### Admin Endpoints

- `GET /he-thong/aiPromptTemplate/list`
- `POST /he-thong/aiPromptTemplate/save`
- `POST /he-thong/aiPromptTemplate/delete`

### Composition Strategy (Token-based)

Prompt is compiled from `template_content` with dynamic tokens:

- `[source]` (required token; auto-appended if missing)
- `[target_language]`
- `[locale]`
- `[seo_type]`

This supports arbitrary prompt layout/order, not fixed `part 1 + source + part 2`.

### UI Workflow

In translation sidebar panel:

- select model
- select template
- edit one flexible prompt template with tokens
- save template to DB for future runs
- click `Dịch toàn bộ`

Users can keep reusable templates and still override prompt parts ad-hoc per translation run.
