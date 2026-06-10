<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * アフィリエイター本人のログインを必須化する。
 * 管理画面の EnsureAdmin と同じセッション方式。
 */
class EnsureAffiliateLoggedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('affiliate_authenticated')) {
            return redirect()->route('affiliate.login');
        }

        return $next($request);
    }
}
