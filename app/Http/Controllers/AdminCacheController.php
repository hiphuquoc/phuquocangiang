<?php

namespace App\Http\Controllers;

use App\Services\HtmlCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminCacheController extends Controller {

    /**
     * Xoá toàn bộ cache HTML qua HtmlCacheService.
     * Phương thức này thay thế logic glob() cũ — hoạt động được với mọi disk
     * (local, gcs) và xoá cả file .gz.
     */
    public static function clear(Request $request = null){
        try {
            $deleted = app(HtmlCacheService::class)->clearAll();
            if ($request && $request->ajax()) {
                return response()->json(['success' => true, 'deleted' => $deleted]);
            }
            return $deleted >= 0;
        } catch (\Throwable $e) {
            Log::error('AdminCacheController::clear failed: ' . $e->getMessage());
            if ($request && $request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return false;
        }
    }
}
