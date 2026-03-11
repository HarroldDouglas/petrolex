<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMobileDevToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Mobile-Dev-Token');
        $expected = config('app.mobile_dev_token');

        if (! $token || ! $expected || ! hash_equals($expected, $token)) {
            return response()->json([
                '_metadata' => [
                    'success' => false,
                    'message' => 'Token invalide ou manquant.',
                ],
                'data' => null,
            ], 401);
        }

        return $next($request);
    }
}
