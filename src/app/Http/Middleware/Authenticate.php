<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // URL に admin が含まれていれば管理者用ログインへ
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            // それ以外は一般ユーザー用ログインへ
            return route('login');
        }
    }
}
