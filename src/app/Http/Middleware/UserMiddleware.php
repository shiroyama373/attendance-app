<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ユーザーが認証されていない、または管理者の場合
        if (!auth()->check() || auth()->user()->is_admin) {
            // 管理者ログイン画面にリダイレクト
            return redirect('/admin/login')->with('error', '一般ユーザー専用ページです');
        }

        return $next($request);
    }
}