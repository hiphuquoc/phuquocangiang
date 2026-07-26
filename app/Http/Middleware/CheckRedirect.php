<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Redirect as RedirectModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;

/**
 * CheckRedirect — middleware xử lý 301 redirect từ bảng redirect_info.
 *
 * Thay thế cho cách cũ:
 *   foreach(\App\Models\Redirect::all() as $r){ Route::get(...); }
 * (gây foreach toàn bảng + đăng ký N route mỗi request).
 *
 * Cơ chế:
 *  - So sánh `request->path()` với cột `url_old` trong bảng `redirect_info`.
 *  - Dùng collation `utf8mb4_bin` để khớp chính xác dấu / hoa thường.
 *  - Khớp ưu tiên cả 2 dạng: có dấu `/` đầu và không dấu `/` đầu (để tương
 *    thích dữ liệu lịch sử của hitour vốn lưu cả 2 dạng).
 *  - Trả về 301 nếu khớp, ngược lại để request tiếp tục.
 */
class CheckRedirect
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Skip với route nội bộ (he-thong, sitemap, api, auth, ...)
            $first = $request->segment(1);
            if (in_array($first, ['he-thong', 'auth', 'api', 'sitemap.xml', 'sitemap', 'storage', 'public', 'build', 'fragments'], true)
                || str_contains($request->path(), '/fragments/')
            ) {
                return $next($request);
            }

            if (!Schema::hasTable('redirect_info')) {
                return $next($request);
            }

            $path        = '/' . rawurldecode($request->path());
            $pathNoSlash = ltrim($path, '/');

            $info = RedirectModel::query()
                ->whereRaw('url_old COLLATE utf8mb4_bin IN (?, ?)', [$path, $pathNoSlash])
                ->first();

            if (!empty($info) && !empty($info->url_new)) {
                $newUrl = $info->url_new;
                /* nếu url_new không bắt đầu bằng / hoặc http -> chuẩn hoá */
                if (!preg_match('#^https?://#i', $newUrl) && substr($newUrl, 0, 1) !== '/') {
                    $newUrl = '/' . $newUrl;
                }
                return Redirect::to($newUrl, 301);
            }
        } catch (\Throwable $e) {
            // Không chặn request nếu DB chưa sẵn sàng / đang migrate
            \Log::warning('CheckRedirect middleware failed: ' . $e->getMessage());
        }

        return $next($request);
    }
}
