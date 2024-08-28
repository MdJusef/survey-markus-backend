<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminandCompanyMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = auth()->userOrFail();

            if ($user->role_type == 'SUPER ADMIN' || $user->role_type == 'ADMIN' || $user->role_type == 'COMPANY') {
                return $next($request);
            }

            return response()->json([
                'message' => 'Unauthorized user'
            ], 401);

        } catch (AuthenticationException $exception) {
            return response()->json([
                'message' => 'Unauthorized: ' . $exception->getMessage()
            ], 401);
        }
    }
}
