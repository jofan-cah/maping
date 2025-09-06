<?php

// 1. CREATE MIDDLEWARE
// Run: php artisan make:middleware CheckPermission
// File: app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
namespace App\Http\Middleware;


class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan');
        }

        // Check if user has permission
        if ($user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki akses untuk melakukan aksi ini',
                    'required_permission' => $permission
                ], 403);
            }

            // For web requests
            abort(403, 'Tidak memiliki akses untuk melakukan aksi ini');
        }

        return $next($request);
    }
}
