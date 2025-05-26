<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // If the required role is GUEST and the user is not logged in, allow passage.
        if ($role === User::ROLE_GUEST && !Auth::check()) {
            return $next($request);
        }

        // If user is not logged in and role is not GUEST, deny.
        if (!Auth::check()) {
            abort(403, 'Unauthorized action. Please log in.');
        }

        // User is logged in. Check their role.
        // For now, strict role matching.
        // Consider hierarchical roles later if needed (e.g., super_admin can access admin/user routes).
        if (Auth::user()->role === User::ROLE_SUPER_ADMIN) {
            // Super admin can access any role's routes
            return $next($request);
        }

        if (Auth::user()->role === User::ROLE_ADMIN && ($role === User::ROLE_ADMIN || $role === User::ROLE_USER || $role === User::ROLE_GUEST)) {
            // Admin can access admin, user, and guest routes
            return $next($request);
        }

        if (Auth::user()->role === User::ROLE_USER && ($role === User::ROLE_USER || $role === User::ROLE_GUEST)) {
            // User can access user and guest routes
            return $next($request);
        }
        
        // If specific role match is required (and not covered by hierarchy above)
        if (Auth::user()->role === $role) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. You do not have the required role.');
    }
}
