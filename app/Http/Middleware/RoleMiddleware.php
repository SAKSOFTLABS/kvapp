<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request based on user roles and designation permissions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Comma separated roles e.g. 'admin' or 'admin,front_office,qc'
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')->withErrors(['username' => 'Your account is deactivated. Please contact administrator.']);
        }

        // Super Admin has full access to all sections
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Explode allowed roles e.g. 'admin', 'staff', 'front_office', 'qc', 'service'
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        // Resolve user's effective designation role
        $effectiveRole = $user->role;
        if (($effectiveRole === 'staff' || empty($effectiveRole)) && $user->staff) {
            $designation = $user->staff->designation;
            if ($designation === 'Front Office') $effectiveRole = 'front_office';
            elseif ($designation === 'QC') $effectiveRole = 'qc';
            elseif ($designation === 'Service') $effectiveRole = 'service';
        }

        // Check if effective role or general 'staff' is allowed
        if (in_array($effectiveRole, $allowedRoles) || in_array('staff', $allowedRoles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        abort(403, 'Unauthorized access to this section based on your user designation permission level.');
    }
}
