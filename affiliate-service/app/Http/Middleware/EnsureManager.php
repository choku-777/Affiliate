<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 「管理」ロール限定の領域（設定変更など）を保護する。
 * 「運用」ロールはアクセス不可。
 */
class EnsureManager
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('admin_role') !== 'manager') {
            abort(403, 'この操作は管理者のみ可能です。');
        }

        return $next($request);
    }
}
