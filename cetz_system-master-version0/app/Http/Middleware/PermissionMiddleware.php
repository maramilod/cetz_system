<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!Auth::check()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'غير مسجل الدخول'], 401)
                : abort(401, 'غير مسجل الدخول');
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasPermission($permission)) {
            return $request->expectsJson()
                ? response()->json([
                    'message' => 'ليس لديك صلاحية',
                    'required_permission' => $permission,
                ], Response::HTTP_FORBIDDEN)
                : abort(403, 'ليس لديك صلاحية');
        }

        return $next($request);
    }
}
