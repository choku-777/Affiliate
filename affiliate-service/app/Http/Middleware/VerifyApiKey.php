<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EC-CUBE プラグインからの postback を X-Api-Key で認証する。
 */
class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('affiliate.api_key');
        $given = (string) $request->header('X-Api-Key');

        if ($expected === '' || !hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
