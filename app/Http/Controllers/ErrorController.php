<?php

namespace App\Http\Controllers;
use App\Models\Seo;
use Illuminate\Http\Request;
use App\Models\CheckSeo as CheckSeoModel;
use Illuminate\Support\Facades\Redirect;

class ErrorController extends Controller {

    public static function error404(){
        $request = request();
        $path    = $request->path();

        /* Fragment / AJAX: không redirect về trang chủ (tránh inject full HTML vào placeholder). */
        if ($request->ajax()
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || str_starts_with($path, 'fragments/')
            || preg_match('#^[a-z]{2}(?:-[a-z]{2})?/fragments/#i', $path)
        ) {
            return response('Not Found', 404);
        }

        return Redirect::to(route('main.home'), 301);
    }

}
