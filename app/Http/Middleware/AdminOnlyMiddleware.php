<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            throw new UnauthorizedException('No user found error.', 401);
        }

        if (!$user->role || $user->role->name !== 'admin') {
            throw new UnauthorizedException('You do not have the required role to access this resource.', 403);
        }

        return $next($request);
    }
}
