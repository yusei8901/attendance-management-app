<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class AdminLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // 管理者ログイン時
        if (Auth::guard('admin')->check()) {
            return $request->wantsJson()
                ? response()->json(['two_factor' => false])
                : redirect()->intended('/admin/attendance/list');
        }
        // 一般ユーザー
        if (Auth::guard('web')->check()) {
            return $request->wantsJson()
                ? response()->json(['two_factor' => false])
                : redirect()->intended('/attendance');
        }
        // 万一どちらでもない場合
        return redirect('/');
    }
}
