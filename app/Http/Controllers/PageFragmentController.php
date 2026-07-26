<?php

namespace App\Http\Controllers;

use App\Services\PageFragmentRegistry;
use Illuminate\Http\Request;

/**
 * HTML partials theo currency (không cache toàn trang).
 * Fragment URL có X-Robots-Tag: noindex — nội dung chính nằm ở URL canonical.
 */
class PageFragmentController extends Controller
{
    public function show(
        Request $request,
        PageFragmentRegistry $registry,
        string $pageType,
        int $seoId
    ) {
        if (!in_array($pageType, PageFragmentRegistry::PAGE_TYPES, true)) {
            return response('<!-- invalid page type -->', 404, ['X-Robots-Tag' => 'noindex']);
        }

        $section = (string) $request->query('section', '');
        $provider = $registry->get($pageType);

        if (!in_array($section, $provider->sections(), true)) {
            return response('<!-- invalid section -->', 404, ['X-Robots-Tag' => 'noindex']);
        }

        try {
            $item = $provider->loadBySeoId($seoId);
            if ($item === null) {
                return response('<!-- not found -->', 404, ['X-Robots-Tag' => 'noindex']);
            }

            $html = $provider->render($section, $item);
        } catch (\Throwable $e) {
            report($e);

            return response('<!-- fragment error -->', 500, ['X-Robots-Tag' => 'noindex']);
        }

        return response($html ?? '', 200, [
            'Content-Type'  => 'text/html; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Robots-Tag'  => 'noindex, nofollow',
            'Vary'          => 'Cookie',
        ]);
    }

    /** @deprecated Giữ route cũ — delegate sang show(). */
    public function tourLocation(Request $request, int $seoId)
    {
        return $this->show($request, app(PageFragmentRegistry::class), 'tour-location', $seoId);
    }
}
