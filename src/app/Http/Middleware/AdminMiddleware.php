<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ユーザーが認証されていない、または管理者でない場合
        if (!auth()->check() || !auth()->user()->is_admin) {
            // 一般ユーザーのログイン画面にリダイレクト
            return redirect('/login')->with('error', '管理者権限が必要です');
        }

        return $next($request);
    }
}

