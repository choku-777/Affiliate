<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理画面の認可。Googleログイン済み（許可メール）でなければログインへ。
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('admin_authenticated')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
