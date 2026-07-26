<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;

/**
 * DetectLocale middleware.
 *
 * Phát hiện locale theo URL prefix (subfolder strategy):
 *  - /                  -> default locale (vi, không prefix)
 *  - /en/...            -> en
 *  - /zh/...            -> zh
 *
 * Xử lý:
 *  1. Nếu locale trong route param hợp lệ và đang active -> setLocale.
 *  2. Nếu không -> dùng default; locale param có thể được rewrite về default.
 *  3. Lưu vào view share để mọi blade đều có $currentLocale + $availableLocales.
 *
 * Cách dùng (route group):
 *   Route::prefix('{locale}')
 *        ->where(['locale' => 'en|zh|ja|ko'])
 *        ->middleware(['detectLocale'])
 *        ->group(function() { ... });
 *
 *   Route::middleware(['detectLocale'])->group(function() { ... });   // routes mặc định
 */
class DetectLocale
{
    public function handle(Request $request, Closure $next)
    {
        $defaultCode = config('language.default_code', 'vi');

        /* Fragment AJAX: ?locale=en — không có prefix /en/ trên path */
        $queryLocale = $request->query('locale');
        if (is_string($queryLocale) && $queryLocale !== '') {
            $langFromQuery = Language::byCode($queryLocale);
            if ($langFromQuery && $langFromQuery->is_active) {
                return $this->applyLocale($request, $next, $langFromQuery, $langFromQuery->code);
            }
        }

        // 1) Lấy locale từ route param hoặc segment đầu URL
        $localeParam = $request->route('locale');
        if (empty($localeParam)) {
            $first = $request->segment(1);
            // Chỉ coi segment đầu là locale nếu nó match 1 ngôn ngữ active
            if ($first && Language::byCode($first)) {
                $localeParam = $first;
            }
        }

        // 2) Validate
        $lang = !empty($localeParam) ? Language::byCode($localeParam) : null;
        if (!$lang || !$lang->is_active) {
            $lang = Language::default();
        }

        $code = $lang ? $lang->code : $defaultCode;

        return $this->applyLocale($request, $next, $lang, $code);
    }

    private function applyLocale(Request $request, Closure $next, ?Language $lang, string $code): mixed
    {
        app()->setLocale($code);

        view()->share('currentLocale', $code);
        view()->share('currentLanguage', $lang);
        view()->share('availableLocales', Language::active());

        $request->attributes->set('locale', $code);
        $request->attributes->set('language_id', $lang?->id);

        return $next($request);
    }
}
