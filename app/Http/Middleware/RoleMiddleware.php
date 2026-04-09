<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = auth()->user();

        if (!$user || $user->role !== $role) {
            return response()->json([
                'message' => 'Unauthorized role'
            ], 403);
        }

        return $next($request);
    }
}