<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 300;

    public function showLoginForm(): View|\Illuminate\Http\RedirectResponse {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.booking.list');
        }
        return view('layouts.loginForm');
    }

    public function loginAdmin(Request $request): JsonResponse {
        $throttleKey = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau.',
                'type' => 'rate_limit',
                'retry_after' => $seconds,
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6|max:100',
        ], [
            'email.required' => 'Vui lòng nhập email',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'type' => 'validation',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $loginValue = trim((string) $request->input('email'));
        $password = (string) $request->input('password');
        $remember = $request->boolean('remember', false);

        $user = \App\Models\User::query()
            ->where(function ($query) use ($loginValue) {
                $query->where('username', $loginValue)
                    ->orWhere('email', $loginValue);
            })
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            if (!$user->isAdmin()) {
                RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn không có quyền truy cập khu vực quản trị.',
                    'type' => 'unauthorized',
                ], 403);
            }

            Auth::login($user, $remember);
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công! Đang chuyển hướng...',
                'redirect_url' => route('admin.booking.list'),
            ]);
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
        return response()->json([
            'success' => false,
            'message' => 'Tên đăng nhập/Email hoặc mật khẩu không chính xác.',
            'type' => 'credentials',
        ], 401);
    }

    public function loginCustomer(Request $request){
        $flag       = false;
        $message    = 'Email và Password không hợp lệ!';
        $dataForm   = [];
        foreach($request->get('data') as $value){
            $dataForm[$value['name']] = $value['value'];
        }
        /* đăng nhập */
        if(Auth::attempt($dataForm)) $flag   = true;
        $result['flag']     = $flag;
        $result['message']  = $message;
        return json_encode($result);
    }

    public static function logout(){
        Auth::logout();
        return redirect($_SERVER['HTTP_REFERER']);
    }
}
