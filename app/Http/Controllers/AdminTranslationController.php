<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\AiPromptTemplate;
use App\Models\Seo;
use App\Models\SeoContentTranslation;
use App\Models\SeoTranslation;
use App\Helpers\SeoAlternates;
use App\Services\Ai\AiGatewayService;
use App\Services\EntityTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * AdminTranslationController — Trang DỊCH cho từng ngôn ngữ.
 *
 * V3.1: REUSE FORM ADMIN GỐC.
 *   Trang dịch = render lại form admin gốc (admin.tour.view, admin.shipLocation.view, ...)
 *   với app locale đã set sang locale đích. Magic accessor của trait HasTranslations
 *   tự động trả về giá trị bản dịch cho các field trong $translatableFields.
 *
 *   Layout admin (admin.layouts.main) detect biến $translationMode → render banner
 *   trên đầu trang + nạp script translation-mode.js làm các việc:
 *     - Override form action gốc → admin.translation.save/{locale}/{seoId}
 *     - Disable mọi input có name không nằm trong whitelist TRANSLATABLE_FIELDS
 *     - Visual highlight các input dịch được
 *     - Ẩn các nút thao tác master data (xoá tour, đổi giá, ...) trong context dịch
 *
 *   Server-side persist (save action) parse form gốc theo config translation_relations
 *   để biết input name nào → translation table nào.
 *
 * URL:
 *   GET  /he-thong/translation/{locale}/{seoId}                    -> render form gốc + context dịch
 *   POST /he-thong/translation/{locale}/{seoId}                    -> save translations
 *   GET  /he-thong/translation/{locale}/{seoId}/ai-source        -> danh sách trường cần dịch (metadata)
 *   POST /he-thong/translation/{locale}/{seoId}/ai-translate-field -> dịch 1 trường (tránh timeout)
 *   POST /he-thong/translation/{locale}/{seoId}/ai-draft         -> dịch bulk (legacy)
 *   GET  /he-thong/translation/{locale}/{seoId}/delete           -> xoá bản dịch
 *
 * Bypass: locale == default_locale → redirect về admin gốc.
 */
class AdminTranslationController extends Controller
{
    /**
     * Map seo.type → [ControllerClass, actionMethod].
     * Action method nhận Request với param `id` (entity id).
     */
    protected static array $TYPE_VIEW_MAP = [
        'tour_info'           => [\App\Http\Controllers\AdminTourController::class,            'view'],
        'tour_info_foreign'   => [\App\Http\Controllers\AdminTourInfoForeignController::class, 'view'],
        'tour_location'       => [\App\Http\Controllers\AdminTourLocationController::class,    'view'],
        'tour_continent'      => [\App\Http\Controllers\AdminTourContinentController::class,   'view'],
        'tour_country'        => [\App\Http\Controllers\AdminTourCountryController::class,     'view'],
        'tour_partner'        => [\App\Http\Controllers\AdminTourPartnerController::class,     'view'],
        'tour_departure'      => [\App\Http\Controllers\AdminTourDepartureController::class,   'view'],
        'ship_info'           => [\App\Http\Controllers\AdminShipController::class,            'view'],
        'ship_location'       => [\App\Http\Controllers\AdminShipLocationController::class,    'view'],
        'ship_partner'        => [\App\Http\Controllers\AdminShipPartnerController::class,     'view'],
        'ship_departure'      => [\App\Http\Controllers\AdminShipDepartureController::class,   'view'],
        'ship_port'           => [\App\Http\Controllers\AdminShipPortController::class,        'view'],
        'service_info'        => [\App\Http\Controllers\AdminServiceController::class,         'view'],
        'service_location'    => [\App\Http\Controllers\AdminServiceLocationController::class, 'view'],
        'air_info'            => [\App\Http\Controllers\AdminAirController::class,             'view'],
        'air_location'        => [\App\Http\Controllers\AdminAirLocationController::class,     'view'],
        'air_partner'         => [\App\Http\Controllers\AdminAirPartnerController::class,      'view'],
        'air_departure'       => [\App\Http\Controllers\AdminAirDepartureController::class,    'view'],
        'air_port'            => [\App\Http\Controllers\AdminAirPortController::class,         'view'],
        'combo_info'          => [\App\Http\Controllers\AdminComboInfoController::class,       'view'],
        'combo_location'      => [\App\Http\Controllers\AdminComboLocationController::class,   'view'],
        'combo_partner'       => [\App\Http\Controllers\AdminComboPartnerController::class,    'view'],
        'hotel_info'          => [\App\Http\Controllers\AdminHotelInfoController::class,       'view'],
        'hotel_location'      => [\App\Http\Controllers\AdminHotelLocationController::class,   'view'],
        'guide_info'          => [\App\Http\Controllers\AdminGuideController::class,           'view'],
        'category_info'       => [\App\Http\Controllers\AdminCategoryController::class,        'view'],
        'blog_info'           => [\App\Http\Controllers\AdminBlogController::class,            'view'],
        'page_info'           => [\App\Http\Controllers\AdminPageController::class,            'view'],
        'carrental_location'  => [\App\Http\Controllers\AdminCarrentalLocationController::class,'view'],
    ];

    /* ===== GET form chỉnh sửa bản dịch (REUSE FORM GỐC) ===== */
    public function edit(Request $request, string $locale, int $seoId)
    {
        $defaultCode = config('language.default_code', 'vi');
        if ($locale === $defaultCode) {
            // Default locale → chuyển admin về form gốc
            $back = self::backUrlForType(optional(Seo::find($seoId))->type ?? '', 0);
            return redirect($back !== '#' ? $back : '/he-thong');
        }

        [$seo, $entity, $cfg, $language] = $this->resolve($locale, $seoId);
        if (!$seo || !$entity || !$cfg || !$language) {
            abort(404, 'Không tìm thấy entity hoặc ngôn ngữ.');
        }

        // Set app locale → magic accessor của HasTranslations trả về bản dịch tự động
        app()->setLocale($locale);
        // Ưu tiên URL từ seo_translations(locale); fallback helper legacy.
        $previewUrl = SeoAlternates::urlFor($seo, $locale);
        if (empty($previewUrl) && function_exists('seo_url_full')) {
            $previewUrl = seo_url_full($entity, $locale);
        }

        // Build whitelist các input name dịch được (cho JS)
        $translatableInputs = $this->buildTranslatableInputWhitelist($cfg);

        // Body content (seo_content_translations): nội dung trang dạng HTML/Blade
        $bodyTrans = SeoContentTranslation::forLocale($seo->id, $locale);
        $bodyDefault = SeoContentTranslation::forLocale($seo->id, $defaultCode);
        $supportsBodyTranslation = !in_array($seo->type, ['tour_info', 'tour_info_foreign'], true);

        // Trạng thái dịch theo language_id (cho switcher hiển thị ✓ / nền xám)
        $statusMap = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('seo_translations')) {
            $rows = DB::table('seo_translations')
                ->where('seo_id', $seo->id)
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->pluck('language_id')
                ->all();
            foreach ($rows as $lid) $statusMap[(int) $lid] = true;
        }

        // Inject context cho layout
        view()->share([
            'translationMode'         => true,
            'translationLocale'       => $locale,
            'translationLanguage'     => $language,
            'translationSeo'          => $seo,
            'translationEntity'       => $entity,
            'translationConfig'       => $cfg,
            'translationDefaultCode'  => $defaultCode,
            'translationDefaultLang'  => Language::byCode($defaultCode),
            'translatableInputs'      => $translatableInputs,
            'translationAllLanguages' => Language::active(),
            'translationBodyContent'  => $bodyTrans ? (string) $bodyTrans->content : '',
            'translationBodyDefault'  => $bodyDefault ? (string) $bodyDefault->content : '',
            'translationSupportsBodyContent' => $supportsBodyTranslation,
            'translationPreviewUrl'   => $previewUrl,
            'translationStatusMap'    => $statusMap,
        ]);

        // Delegate sang controller gốc để reuse view + setup data (locations, partners, ...)
        $map = self::$TYPE_VIEW_MAP[$seo->type] ?? null;
        if (empty($map)) {
            abort(501, 'Loại trang "' . $seo->type . '" chưa hỗ trợ giao diện dịch (chưa khai báo TYPE_VIEW_MAP).');
        }
        [$ctrlClass, $action] = $map;

        // Build request giả với id của entity gốc + type=edit
        $delegateRequest = Request::create($request->getRequestUri(), 'GET', [
            'id'   => $entity->id,
            'type' => 'edit',
            '_translation_mode' => 1,
            '_translation_locale' => $locale,
        ]);

        $controller = app($ctrlClass);
        return app()->call([$controller, $action], ['request' => $delegateRequest]);
    }

    /**
     * Build whitelist các input name có thể dịch.
     * Dùng cho JS auto-disable bên FE.
     *
     * Bao gồm:
     *  - SEO fields (title, description, seo_title, seo_description, slug, link_canonical)
     *  - Entity translatable (config translatable)
     *  - Tất cả field dịch được trong các relation (config translation_relations.<rel>.fields)
     *  - Các input pattern đặc biệt theo entity (form gốc có thể đặt name khác tên cột — khai
     *    báo qua key `input_aliases` trong config translation_relations[<rel>]).
     */
    private function buildTranslatableInputWhitelist(array $cfg): array
    {
        $list = [
            // SEO fields chuẩn
            'title', 'description', 'seo_title', 'seo_description', 'slug', 'link_canonical',
        ];
        // Entity gốc
        foreach (($cfg['translatable'] ?? []) as $f) $list[] = $f;
        foreach (($cfg['translatable_input_aliases'] ?? []) as $alias) $list[] = $alias;
        foreach (($cfg['translatable_inputs'] ?? []) as $inputName) $list[] = $inputName;

        // Relations
        foreach (($cfg['translation_relations'] ?? []) as $key => $rel) {
            foreach (($rel['fields'] ?? []) as $f) $list[] = $f;
            foreach (($rel['input_aliases'] ?? []) as $alias) $list[] = $alias;
            foreach (array_keys($rel['input_field_aliases'] ?? []) as $inputName) $list[] = $inputName;
            if (!empty($rel['input_id_alias'])) $list[] = $rel['input_id_alias'];
        }
        return array_values(array_unique($list));
    }

    /* ===== POST save bản dịch =====
     *
     * V3.1: parse form gốc (không phải custom nested namespace).
     *
     * Cấu trúc input nhận diện theo `config('tablemysql.<type>')`:
     *  - SEO fields top-level: title, description, seo_title, seo_description, slug, link_canonical → seo_translations
     *  - Entity translatable top-level (name, pick_up, transport, ...) → <entity>_translations
     *  - Mỗi block trong `translation_relations` có thể khai báo:
     *      input_layout = 'top_level'  → field name match field DB ngay ở root POST
     *      input_layout = 'array'      → field nằm trong array, có id sentinel
     *      input_layout = 'ajax'       → bỏ qua (AJAX modal flow xử lý riêng)
     *
     * Body content riêng:
     *  - Input `body_content_translation` (textarea custom inject vào form gốc qua banner)
     *    → seo_content_translations.
     */
    public function save(Request $request, string $locale, int $seoId)
    {
        [$seo, $entity, $cfg, $language] = $this->resolve($locale, $seoId);
        if (!$seo || !$entity || !$language) {
            abort(404);
        }
        if ($locale === config('language.default_code', 'vi')) {
            return back()->withErrors(['locale' => 'Không thể chỉnh sửa bản dịch của ngôn ngữ mặc định ở trang này.']);
        }

        try {
            DB::transaction(function () use ($request, $seo, $entity, $language, $locale, $cfg) {
                $this->persistSeoTranslation($request, $seo, $locale);
                $this->persistEntityTranslation($request, $entity, $locale, $cfg);
                $this->persistBodyContent($request, $seo, $language);
                $this->persistLegacyScheduleContent($request, $seo, $locale);
                $this->persistAllRelations($request, $entity, $language, $cfg);
            });
        } catch (\Throwable $e) {
            Log::error('AdminTranslation save failed: ' . $e->getMessage(), [
                'seo_id' => $seoId, 'locale' => $locale, 'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['save' => 'Lỗi khi lưu bản dịch: ' . $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Đã lưu bản dịch ' . strtoupper($locale) . ' thành công.');
    }

    /**
     * Danh sách trường cần dịch (metadata, không gọi AI).
     * FE dùng để hiển thị tiến độ rồi gọi aiTranslateField từng job.
     */
    public function aiSource(Request $request, string $locale, int $seoId)
    {
        if ($err = $this->aiTranslationLocaleError($locale)) {
            return $err;
        }

        $ctx = $this->resolveAiTranslationContext($locale, $seoId);
        if ($ctx === null) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy entity cần dịch.'], 404);
        }

        [$seo, $entity, $cfg, , $defaultCode] = $ctx;
        $jobs = $this->flattenAiSourceToJobs($this->buildAiSourcePayload($seo, $entity, $cfg, $defaultCode));

        return response()->json([
            'success' => true,
            'data' => [
                'total' => count($jobs),
                'jobs' => array_map(static function (array $job): array {
                    return [
                        'key' => $job['key'],
                        'kind' => $job['kind'],
                        'label' => $job['label'],
                        'input_name' => $job['input_name'],
                        'array_name' => $job['array_name'] ?? null,
                        'row_id' => $job['row_id'] ?? null,
                        'id_alias' => $job['id_alias'] ?? null,
                    ];
                }, $jobs),
            ],
        ]);
    }

    /**
     * Dịch một trường (một request AI) — tránh timeout nginx khi dịch cả trang.
     */
    public function aiTranslateField(Request $request, string $locale, int $seoId)
    {
        if ($err = $this->aiTranslationLocaleError($locale)) {
            return $err;
        }

        $ctx = $this->resolveAiTranslationContext($locale, $seoId);
        if ($ctx === null) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy entity cần dịch.'], 404);
        }

        [$seo, $entity, $cfg, $language, $defaultCode] = $ctx;
        $key = trim((string) $request->input('key', ''));
        if ($key === '') {
            return response()->json(['success' => false, 'message' => 'Thiếu key trường cần dịch.'], 422);
        }

        $jobs = $this->flattenAiSourceToJobs($this->buildAiSourcePayload($seo, $entity, $cfg, $defaultCode));
        $job = null;
        foreach ($jobs as $row) {
            if (($row['key'] ?? '') === $key) {
                $job = $row;
                break;
            }
        }
        if ($job === null) {
            return response()->json(['success' => false, 'message' => 'Trường không hợp lệ hoặc không có nội dung gốc.'], 404);
        }

        try {
            $promptTemplate = $this->resolvePromptTemplate($request, $seo, $locale);
            $languageName = $language->name ?? strtoupper($locale);
            $selectedModel = (string) $request->input('model', '');
            $withDebug = $this->aiTranslationDebugRequested($request);
            $translation = $this->translateAiText(
                (string) ($job['source'] ?? ''),
                $locale,
                $languageName,
                $selectedModel,
                $promptTemplate,
                $seo->type,
                ($job['kind'] ?? '') === 'body',
                $withDebug
            );

            $payload = [
                'success' => true,
                'data' => [
                    'key' => $job['key'],
                    'kind' => $job['kind'],
                    'label' => $job['label'],
                    'input_name' => $job['input_name'],
                    'array_name' => $job['array_name'] ?? null,
                    'row_id' => $job['row_id'] ?? null,
                    'id_alias' => $job['id_alias'] ?? null,
                    'translated' => $translation['content'],
                ],
            ];
            if (!empty($translation['debug'])) {
                $payload['debug'] = array_merge($translation['debug'], [
                    'field_key' => $job['key'],
                    'label' => $job['label'],
                ]);
            }

            return response()->json($payload);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            Log::error('AdminTranslation aiTranslateField failed: ' . $e->getMessage(), [
                'seo_id' => $seoId, 'locale' => $locale, 'key' => $key,
            ]);
            return response()->json(['success' => false, 'message' => 'Có lỗi khi dịch trường này.'], 500);
        }
    }

    /**
     * AI draft translation (legacy bulk) — giữ tương thích API cũ.
     * Khuyến nghị FE dùng aiSource + aiTranslateField để tránh 502.
     */
    public function aiDraft(Request $request, string $locale, int $seoId)
    {
        if ($err = $this->aiTranslationLocaleError($locale)) {
            return $err;
        }

        $ctx = $this->resolveAiTranslationContext($locale, $seoId);
        if ($ctx === null) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy entity cần dịch.'], 404);
        }

        [$seo, $entity, $cfg, $language, $defaultCode] = $ctx;

        try {
            $source = $this->buildAiSourcePayload($seo, $entity, $cfg, $defaultCode);
            $promptTemplate = $this->resolvePromptTemplate($request, $seo, $locale);
            $translated = $this->translateAiPayload(
                $source,
                $locale,
                $language->name ?? strtoupper($locale),
                (string) $request->input('model', ''),
                $promptTemplate,
                $seo->type
            );

            return response()->json([
                'success' => true,
                'data' => $translated,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            Log::error('AdminTranslation aiDraft failed: ' . $e->getMessage(), [
                'seo_id' => $seoId, 'locale' => $locale, 'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Có lỗi khi gọi AI draft.'], 500);
        }
    }

    private function aiTranslationLocaleError(string $locale): ?\Illuminate\Http\JsonResponse
    {
        if ($locale === config('language.default_code', 'vi')) {
            return response()->json([
                'success' => false,
                'message' => 'Không hỗ trợ AI draft trên ngôn ngữ mặc định.',
            ], 422);
        }

        return null;
    }

    /**
     * @return array{0:Seo,1:\Illuminate\Database\Eloquent\Model,2:array,3:Language,4:string}|null
     */
    private function resolveAiTranslationContext(string $locale, int $seoId): ?array
    {
        [$seo, $entity, $cfg, $language] = $this->resolve($locale, $seoId);
        if (!$seo || !$entity || !$cfg || !$language) {
            return null;
        }

        $defaultCode = config('language.default_code', 'vi');

        return [$seo, $entity, $cfg, $language, $defaultCode];
    }

    /* === SEO translation (top-level: title/description/seo_title/seo_description/slug/link_canonical) === */
    private function persistSeoTranslation(Request $request, Seo $seo, string $locale): void
    {
        $seoFields = ['title', 'description', 'seo_title', 'seo_description', 'slug', 'link_canonical'];
        $payload = [];
        foreach ($seoFields as $f) {
            $v = $request->input($f);
            if ($v !== null && $v !== '') $payload[$f] = $v;
        }
        if (!empty($payload)) {
            $payload['status'] = $request->input('seo_status', 'published');
            EntityTranslationService::saveSeoTranslation($seo->id, $locale, $payload);
        }
    }

    /* === Entity translatable fields top-level ===
     * Hỗ trợ alias: 1 số entity có cột DB không trùng input name của form gốc.
     * Vd: tour.name được derive từ input "title" (do form gốc không có ô "name" riêng).
     * Khai báo qua $cfg['translatable_input_aliases'] = ['name' => 'title', ...].
     *
     * Auto-fallback: nếu 1 field translatable không có input trùng tên + không có alias,
     * thử input 'title' (heuristic: hầu hết entity admin form đặt tên trang ở input title).
     */
    private function persistEntityTranslation(Request $request, $entity, string $locale, array $cfg): void
    {
        $allowed = $cfg['translatable'] ?? [];
        if (empty($allowed)) return;

        $aliases = $cfg['translatable_input_aliases'] ?? [];
        $payload = [];
        foreach ($allowed as $f) {
            $inputName = $aliases[$f] ?? $f;
            $v = $request->input($inputName);
            // Auto-fallback: nếu là 'name' không có input trực tiếp, thử 'title'
            if (($v === null || $v === '') && $f === 'name' && !isset($aliases[$f])) {
                $v = $request->input('title');
            }
            if ($v !== null && $v !== '') $payload[$f] = $v;
        }
        if (!empty($payload)) {
            EntityTranslationService::saveEntityTranslation($entity, $locale, $payload);
        }
    }

    /* === Body content (seo_content_translations) === */
    private function persistBodyContent(Request $request, Seo $seo, Language $language): void
    {
        if (!$request->has('body_content_translation')) return;
        SeoContentTranslation::updateOrCreate(
            ['seo_id' => $seo->id, 'language_id' => $language->id],
            [
                'content'       => (string) $request->input('body_content_translation'),
                'status'        => 'published',
                'translated_by' => (string) (Auth::id() ?? 'manual'),
            ]
        );
    }

    /**
     * Legacy schedule content cho ship pages:
     * - Input: schedule (textarea formSchedule)
     * - Storage:
     *   + default locale: <slug>.blade.php
     *   + locale khác:    <slug>.<locale>.blade.php
     */
    private function persistLegacyScheduleContent(Request $request, Seo $seo, string $locale): void
    {
        if (!$request->has('schedule')) return;
        if (!in_array($seo->type, ['ship_info', 'ship_location'], true)) return;

        $schedule = (string) $request->input('schedule', '');
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $lang = Language::byCode($locale);
            if ($lang) {
                $t = SeoTranslation::query()
                    ->where('seo_id', $seo->id)
                    ->where('language_id', $lang->id)
                    ->first();
                $slug = trim((string) ($t->slug ?? ''));
            }
        }
        if ($slug === '') {
            $slug = trim((string) ($seo->getRawOriginal('slug') ?? $seo->slug ?? ''));
        }
        if ($slug === '') return;

        $defaultCode = config('language.default_code', 'vi');
        $dir = config('admin.storage.contentSchedule');
        if (empty($dir)) return;

        $path = $dir . $slug . '.blade.php';
        if ($locale !== $defaultCode) {
            $path = $dir . $slug . '.' . $locale . '.blade.php';
        }
        Storage::put($path, $schedule);
    }

    /* === All translation_relations defined in config === */
    private function persistAllRelations(Request $request, $entity, Language $language, array $cfg): void
    {
        foreach (($cfg['translation_relations'] ?? []) as $key => $rel) {
            $layout = $rel['input_layout'] ?? 'top_level';
            switch ($layout) {
                case 'top_level':
                    // Mỗi field map 1-1 với cột DB của relation (1 row duy nhất, hasOne)
                    $this->persistRelTopLevel($request, $entity, $language, $rel);
                    break;
                case 'array':
                    // Field nằm trong $request->input(<array_name>)[idx][<field>]
                    $this->persistRelArray($request, $entity, $language, $rel);
                    break;
                case 'ajax':
                    // Bỏ qua — AJAX modal endpoint xử lý riêng
                    break;
            }
        }
    }

    private function persistRelTopLevel(Request $request, $entity, Language $language, array $rel): void
    {
        $modelClass = $rel['model'] ?? null;
        $fk         = $rel['fk']    ?? null;
        if (!$modelClass || !class_exists($modelClass) || !$fk) return;

        // Lấy 1 row hiện có (hasOne) hoặc skip nếu chưa có
        $query = $modelClass::where($fk, $entity->id);
        foreach (($rel['extra_filter'] ?? []) as $k => $v) $query = $query->where($k, $v);
        $instance = $query->first();
        if (!$instance) return;

        $payload = [];
        foreach (($rel['fields'] ?? []) as $f) {
            $inputName = $rel['input_aliases'][$f] ?? $f;
            $v = $request->input($inputName);
            if ($v !== null && $v !== '') $payload[$f] = $v;
        }
        if (empty($payload)) return;

        $payload['status']        = 'published';
        $payload['translated_by'] = (string) (Auth::id() ?? 'manual');
        $instance->upsertTranslation($language->id, $payload);
    }

    private function persistRelArray(Request $request, $entity, Language $language, array $rel): void
    {
        $modelClass = $rel['model'] ?? null;
        $fk         = $rel['fk']    ?? null;
        if (!$modelClass || !class_exists($modelClass) || !$fk) return;

        $arrayName = $rel['input_array_name'] ?? null;
        if (!$arrayName) return;
        $items = $request->input($arrayName, []);
        if (!is_array($items) || empty($items)) return;

        $idAlias = $rel['input_id_alias'] ?? null; // tên input chứa id row gốc
        $aliases = $rel['input_field_aliases'] ?? []; // map input_name → db_column

        foreach ($items as $idx => $item) {
            if (!is_array($item)) continue;
            $rowId = null;
            if ($idAlias && !empty($item[$idAlias])) $rowId = (int) $item[$idAlias];
            if (!$rowId) continue; // chỉ dịch cho row đã tồn tại; row mới do form gốc tạo trước (default locale)

            $instance = $modelClass::find($rowId);
            if (!$instance) continue;
            // Security: instance phải thuộc entity gốc
            if ((int) $instance->{$fk} !== (int) $entity->id) continue;

            $payload = [];
            foreach ($aliases as $inputName => $dbField) {
                if ($dbField === '__id') continue;
                if (!array_key_exists($inputName, $item)) continue;
                if ($item[$inputName] === null || $item[$inputName] === '') continue;
                $payload[$dbField] = $item[$inputName];
            }
            if (empty($payload)) continue;

            $payload['status']        = 'published';
            $payload['translated_by'] = (string) (Auth::id() ?? 'manual');
            $instance->upsertTranslation($language->id, $payload);
        }
    }

    /* ===== DELETE: xoá toàn bộ bản dịch của 1 locale (trừ default) ===== */
    public function delete(Request $request, string $locale, int $seoId)
    {
        if ($locale === config('language.default_code', 'vi')) {
            return back()->withErrors(['locale' => 'Không thể xoá bản dịch của ngôn ngữ mặc định.']);
        }

        [$seo, $entity, $cfg, $language] = $this->resolve($locale, $seoId);
        if (!$seo || !$language) abort(404);

        try {
            DB::transaction(function () use ($seo, $entity, $cfg, $language) {
                SeoTranslation::where('seo_id', $seo->id)->where('language_id', $language->id)->delete();
                SeoContentTranslation::where('seo_id', $seo->id)->where('language_id', $language->id)->delete();

                if ($entity && method_exists($entity, 'translations')) {
                    $entity->translations()->where('language_id', $language->id)->delete();
                }
                foreach (($cfg['translation_relations'] ?? []) as $rel) {
                    $modelClass = $rel['model'] ?? null;
                    if (!$modelClass || !class_exists($modelClass)) continue;
                    $instance = new $modelClass();
                    $transClass = method_exists($instance, 'getTranslationModelClass') ? $instance->getTranslationModelClass() : null;
                    if (!$transClass || !class_exists($transClass)) continue;

                    $relIds = $modelClass::where($rel['fk'], $entity->id);
                    foreach (($rel['extra_filter'] ?? []) as $k => $v) $relIds = $relIds->where($k, $v);
                    $relIds = $relIds->pluck('id')->all();
                    if (empty($relIds)) continue;

                    $fkOnTrans = (new $transClass())->entityForeignKey();
                    $transClass::whereIn($fkOnTrans, $relIds)->where('language_id', $language->id)->delete();
                }
            });
        } catch (\Throwable $e) {
            Log::error('AdminTranslation delete failed: ' . $e->getMessage());
            return back()->withErrors(['delete' => 'Lỗi khi xoá: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Đã xoá toàn bộ bản dịch ' . strtoupper($locale) . '.');
    }

    /* ===== Helpers ===== */

    /**
     * @return array{0:?Seo,1:?\Illuminate\Database\Eloquent\Model,2:?array,3:?Language}
     */
    private function resolve(string $locale, int $seoId): array
    {
        $seo = Seo::find($seoId);
        if (!$seo) return [null, null, null, null];

        $cfg = config('tablemysql.' . $seo->type);
        if (empty($cfg) || empty($cfg['model'])) return [$seo, null, null, null];

        $modelClass = $cfg['model'];
        $entity = $modelClass::where('seo_id', $seo->id)->first();

        $language = Language::byCode($locale);
        return [$seo, $entity, $cfg, $language];
    }

    private function buildAiSourcePayload(Seo $seo, $entity, array $cfg, string $defaultCode): array
    {
        $seoFields = ['title', 'description', 'seo_title', 'seo_description', 'slug', 'link_canonical'];
        $fields = [];
        foreach ($seoFields as $f) {
            $raw = $seo->getRawOriginal($f);
            if ($raw !== null && $raw !== '') $fields[$f] = (string) $raw;
        }

        $aliases = $cfg['translatable_input_aliases'] ?? [];
        foreach (($cfg['translatable'] ?? []) as $dbField) {
            $inputName = $aliases[$dbField] ?? $dbField;
            $raw = method_exists($entity, 'getRawOriginal') ? $entity->getRawOriginal($dbField) : ($entity->{$dbField} ?? null);
            if ($raw !== null && $raw !== '') $fields[$inputName] = (string) $raw;
        }

        foreach (($cfg['translatable_inputs'] ?? []) as $inputName) {
            if ($inputName === 'schedule' && in_array($seo->type, ['ship_info', 'ship_location'], true)) {
                $schedule = $this->loadDefaultScheduleContent($seo);
                if ($schedule !== null && $schedule !== '') $fields[$inputName] = $schedule;
            }
        }

        $arrays = [];
        foreach (($cfg['translation_relations'] ?? []) as $rel) {
            $layout = $rel['input_layout'] ?? 'top_level';
            if ($layout === 'top_level') {
                $modelClass = $rel['model'] ?? null;
                $fk = $rel['fk'] ?? null;
                if (!$modelClass || !$fk || !class_exists($modelClass)) continue;
                $query = $modelClass::where($fk, $entity->id);
                foreach (($rel['extra_filter'] ?? []) as $k => $v) $query = $query->where($k, $v);
                $instance = $query->first();
                if (!$instance) continue;
                foreach (($rel['fields'] ?? []) as $dbField) {
                    $inputName = $rel['input_aliases'][$dbField] ?? $dbField;
                    $raw = method_exists($instance, 'getRawOriginal') ? $instance->getRawOriginal($dbField) : ($instance->{$dbField} ?? null);
                    if ($raw !== null && $raw !== '') $fields[$inputName] = (string) $raw;
                }
                continue;
            }

            if ($layout !== 'array') continue;
            $modelClass = $rel['model'] ?? null;
            $fk = $rel['fk'] ?? null;
            $arrayName = $rel['input_array_name'] ?? null;
            $idAlias = $rel['input_id_alias'] ?? null;
            $map = $rel['input_field_aliases'] ?? [];
            if (!$modelClass || !$fk || !$arrayName || !$idAlias || empty($map) || !class_exists($modelClass)) continue;

            $query = $modelClass::where($fk, $entity->id);
            foreach (($rel['extra_filter'] ?? []) as $k => $v) $query = $query->where($k, $v);
            $orderBy = $rel['order_by'] ?? 'id';
            $items = $query->orderBy($orderBy)->get();
            if ($items->isEmpty()) continue;

            $rows = [];
            foreach ($items as $instance) {
                $rowFields = [];
                foreach ($map as $inputName => $dbField) {
                    if ($dbField === '__id') continue;
                    $raw = method_exists($instance, 'getRawOriginal') ? $instance->getRawOriginal($dbField) : ($instance->{$dbField} ?? null);
                    if ($raw !== null && $raw !== '') $rowFields[$inputName] = (string) $raw;
                }
                if (!empty($rowFields)) {
                    $rows[] = ['id' => (int) $instance->id, 'fields' => $rowFields];
                }
            }
            if (!empty($rows)) {
                $arrays[$arrayName] = ['id_alias' => $idAlias, 'rows' => $rows];
            }
        }

        $bodyDefault = SeoContentTranslation::forLocale($seo->id, $defaultCode);
        $bodyContent = $bodyDefault ? (string) $bodyDefault->content : '';

        return [
            'fields' => $fields,
            'arrays' => $arrays,
            'body_content_translation' => $bodyContent,
        ];
    }

    /**
     * Chuyển payload nguồn thành danh sách job dịch từng trường.
     *
     * @return array<int, array<string, mixed>>
     */
    private function flattenAiSourceToJobs(array $source): array
    {
        $jobs = [];

        foreach (($source['fields'] ?? []) as $name => $text) {
            $text = (string) $text;
            if (trim($text) === '') continue;
            $jobs[] = [
                'key' => 'field:' . $name,
                'kind' => 'field',
                'label' => (string) $name,
                'input_name' => (string) $name,
                'source' => $text,
            ];
        }

        foreach (($source['arrays'] ?? []) as $arrayName => $meta) {
            $idAlias = (string) ($meta['id_alias'] ?? '');
            foreach (($meta['rows'] ?? []) as $row) {
                $rowId = (int) ($row['id'] ?? 0);
                foreach (($row['fields'] ?? []) as $inputName => $text) {
                    $text = (string) $text;
                    if (trim($text) === '') continue;
                    $jobs[] = [
                        'key' => 'array:' . $arrayName . ':' . $rowId . ':' . $inputName,
                        'kind' => 'array_row',
                        'label' => $arrayName . '[' . $rowId . '].' . $inputName,
                        'array_name' => (string) $arrayName,
                        'row_id' => $rowId,
                        'id_alias' => $idAlias,
                        'input_name' => (string) $inputName,
                        'source' => $text,
                    ];
                }
            }
        }

        $body = (string) ($source['body_content_translation'] ?? '');
        if (trim($body) !== '') {
            $jobs[] = [
                'key' => 'body',
                'kind' => 'body',
                'label' => 'body_content_translation',
                'input_name' => 'body_content_translation',
                'source' => $body,
            ];
        }

        return $jobs;
    }

    private function translateAiPayload(
        array $source,
        string $locale,
        string $languageName,
        string $selectedModel,
        string $promptTemplate,
        string $seoType
    ): array
    {
        $fieldsTranslated = [];
        foreach (($source['fields'] ?? []) as $k => $v) {
            $fieldsTranslated[$k] = $this->translateAiText(
                (string) $v,
                $locale,
                $languageName,
                $selectedModel,
                $promptTemplate,
                $seoType,
                false
            )['content'];
        }

        $arraysTranslated = [];
        foreach (($source['arrays'] ?? []) as $arrayName => $meta) {
            $rowsOut = [];
            foreach (($meta['rows'] ?? []) as $row) {
                $rowFieldsOut = [];
                foreach (($row['fields'] ?? []) as $k => $v) {
                    $rowFieldsOut[$k] = $this->translateAiText(
                        (string) $v,
                        $locale,
                        $languageName,
                        $selectedModel,
                        $promptTemplate,
                        $seoType,
                        false
                    )['content'];
                }
                $rowsOut[] = ['id' => (int) ($row['id'] ?? 0), 'fields' => $rowFieldsOut];
            }
            $arraysTranslated[$arrayName] = [
                'id_alias' => (string) ($meta['id_alias'] ?? ''),
                'rows' => $rowsOut,
            ];
        }

        return [
            'fields' => $fieldsTranslated,
            'arrays' => $arraysTranslated,
            'body_content_translation' => $this->translateAiText(
                (string) ($source['body_content_translation'] ?? ''),
                $locale,
                $languageName,
                $selectedModel,
                $promptTemplate,
                $seoType,
                true
            )['content'],
            'meta' => ['locale' => $locale, 'language_name' => $languageName],
        ];
    }

    private function aiTranslationDebugRequested(Request $request): bool
    {
        return $request->boolean('debug');
    }

    /**
     * @return array{content:string,debug?:array<string,mixed>}
     */
    private function translateAiText(
        string $text,
        string $locale,
        string $languageName,
        string $selectedModel,
        string $promptTemplate,
        string $seoType,
        bool $allowChunking,
        bool $withDebug = false
    ): array {
        $text = (string) $text;
        if (trim($text) === '') {
            return ['content' => ''];
        }

        if ($allowChunking && mb_strlen($text) > 3500) {
            $parts = $this->splitTextForTranslation($text, 3200);
            $out = '';
            $lastDebug = null;
            foreach ($parts as $i => $part) {
                $chunk = $this->translateAiTextOnce(
                    $part,
                    $locale,
                    $languageName,
                    $selectedModel,
                    $promptTemplate,
                    $seoType,
                    $withDebug && $i === 0
                );
                $out .= $chunk['content'];
                if (!empty($chunk['debug'])) {
                    $lastDebug = $chunk['debug'];
                }
            }

            $result = ['content' => $out];
            if ($lastDebug !== null) {
                $result['debug'] = $lastDebug;
            }

            return $result;
        }

        return $this->translateAiTextOnce(
            $text,
            $locale,
            $languageName,
            $selectedModel,
            $promptTemplate,
            $seoType,
            $withDebug
        );
    }

    /**
     * @return array{content:string,debug?:array<string,mixed>}
     */
    private function translateAiTextOnce(
        string $text,
        string $locale,
        string $languageName,
        string $selectedModel,
        string $promptTemplate,
        string $seoType,
        bool $withDebug = false
    ): array {
        /** @var AiGatewayService $ai */
        $ai = app(AiGatewayService::class);
        $options = [];
        if ($selectedModel !== '') {
            $options['model'] = $selectedModel;
        }
        if ($withDebug) {
            $options['debug'] = true;
        }

        $systemPrompt = 'You are an expert translator. Keep HTML structure, placeholders, and line breaks unchanged unless explicitly instructed.';
        $userPrompt = $this->compilePromptTemplate($promptTemplate, [
            'source' => $text,
            'target_language' => $languageName,
            'locale' => $locale,
            'seo_type' => $seoType,
        ]);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $result = $ai->chat($messages, $options);

        $out = ['content' => (string) ($result['content'] ?? '')];
        if (!empty($result['debug'])) {
            $out['debug'] = array_merge($result['debug'], [
                'field_key' => null,
                'client_model' => $selectedModel,
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ]);
        }

        return $out;
    }

    /**
     * @return array<int, string>
     */
    private function splitTextForTranslation(string $text, int $maxLen): array
    {
        if (mb_strlen($text) <= $maxLen) {
            return [$text];
        }

        $chunks = [];
        $remaining = $text;
        while (mb_strlen($remaining) > $maxLen) {
            $slice = mb_substr($remaining, 0, $maxLen);
            $breakAt = -1;
            foreach (['</p>', '</div>', '</li>', "\n\n", "\n"] as $sep) {
                $pos = mb_strrpos($slice, $sep);
                if ($pos !== false && $pos > (int) ($maxLen * 0.35)) {
                    $breakAt = $pos + mb_strlen($sep);
                    break;
                }
            }
            if ($breakAt <= 0) {
                $breakAt = $maxLen;
            }
            $chunks[] = mb_substr($remaining, 0, $breakAt);
            $remaining = mb_substr($remaining, $breakAt);
        }
        if (trim($remaining) !== '') {
            $chunks[] = $remaining;
        }

        return $chunks;
    }

    private function resolvePromptTemplate(Request $request, Seo $seo, string $locale): string
    {
        $templateText = trim((string) $request->input('prompt_template_text', ''));
        if ($templateText !== '') {
            return $templateText;
        }

        $templateId = (int) $request->input('prompt_template_id', 0);
        if ($templateId > 0) {
            $tpl = AiPromptTemplate::query()->where('id', $templateId)->where('is_active', 1)->first();
            if ($tpl) return $this->normalizeStoredPromptTemplate($tpl);
        }

        $default = AiPromptTemplate::query()
            ->where('scope', 'translation')
            ->where('is_active', 1)
            ->where('is_default', 1)
            ->orderBy('id')
            ->first();
        if ($default) return $this->normalizeStoredPromptTemplate($default);

        return $this->defaultTranslationPromptTemplate();
    }

    private function defaultTranslationPromptTemplate(): string
    {
        $raw = trim((string) config('ai.translation_prompt_default', ''));
        if ($raw !== '') {
            return $raw;
        }

        return <<<'PROMPT'
Dịch nội dung sau sang ngôn ngữ [target_language]
Yêu cầu:
- Chuẩn văn phong, ngôn ngữ địa phương, thông dụng
- Dùng cho website, chuẩn SEO và dễ hiểu
Nội dung cần dịch:
"[source]"
PROMPT;
    }

    private function normalizeStoredPromptTemplate(AiPromptTemplate $tpl): string
    {
        $raw = trim((string) ($tpl->template_content ?? ''));
        if ($raw !== '') return $raw;
        return trim((string) ($tpl->part_before ?? ''))
            . "\n\n[source]\n\n"
            . trim((string) ($tpl->part_after ?? ''));
    }

    private function compilePromptTemplate(string $template, array $vars): string
    {
        $template = trim($template);
        if ($template === '') $template = '[source]';
        if (strpos($template, '[source]') === false) {
            $template .= "\n\n[source]";
        }
        $map = [
            '[source]' => (string) ($vars['source'] ?? ''),
            '[target_language]' => (string) ($vars['target_language'] ?? ''),
            '[locale]' => (string) ($vars['locale'] ?? ''),
            '[seo_type]' => (string) ($vars['seo_type'] ?? ''),
            '[instruction]' => '',
            '[/instruction]' => '',
        ];
        return strtr($template, $map) . "\n\nReturn translated content only.";
    }

    private function loadDefaultScheduleContent(Seo $seo): ?string
    {
        $dir = config('admin.storage.contentSchedule');
        if (empty($dir)) return null;
        $slug = trim((string) ($seo->getRawOriginal('slug') ?? $seo->slug ?? ''));
        if ($slug === '') return null;
        $path = $dir . $slug . '.blade.php';
        if (!Storage::exists($path)) return null;
        return (string) Storage::get($path);
    }

    /**
     * Trả về URL chỉnh sửa entity gốc theo seo.type.
     * Dùng để link "Quay lại trang gốc".
     */
    public static function backUrlForType(string $type, int $entityId): string
    {
        $map = [
            'tour_info'         => 'admin.tour.view',
            'tour_info_foreign' => 'admin.tourInfoForeign.view',
            'tour_location'     => 'admin.tourLocation.view',
            'tour_continent'    => 'admin.tourContinent.view',
            'tour_country'      => 'admin.tourCountry.view',
            'ship_info'         => 'admin.ship.view',
            'ship_location'     => 'admin.shipLocation.view',
            'ship_partner'      => 'admin.shipPartner.view',
            'service_info'      => 'admin.service.view',
            'service_location'  => 'admin.serviceLocation.view',
            'air_info'          => 'admin.air.view',
            'air_location'      => 'admin.airLocation.view',
            'combo_info'        => 'admin.comboInfo.view',
            'combo_location'    => 'admin.comboLocation.view',
            'hotel_info'        => 'admin.hotelInfo.view',
            'hotel_location'    => 'admin.hotelLocation.view',
            'guide_info'        => 'admin.guide.view',
            'category_info'     => 'admin.category.view',
            'blog_info'         => 'admin.blog.view',
            'page_info'         => 'admin.page.view',
            'carrental_location'=> 'admin.carrentalLocation.view',
        ];
        $route = $map[$type] ?? null;
        if (!$route) return '#';
        try { return route($route, ['id' => $entityId]); } catch (\Throwable $e) { return '#'; }
    }
}
