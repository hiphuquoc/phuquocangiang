<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Currency switcher: server-fallback (JS đã làm client-side cookie).
        // An toàn vì chỉ set cookie hiển thị, không thay đổi dữ liệu.
        'currency/switch',
    ];
}
