<?php

namespace App\Http\Middleware;

use App\Enums\AccountType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return $this->unauthorizedResponse($request, 'Authentication required.');
        }

        $user = Auth::user();

        // If no roles specified, just check authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Parse comma-separated roles from single parameter if needed
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode(',', $role));
        }

        // Convert string roles to AccountType enums and check if user has required role
        $userAccountType = $user->account_type;
        foreach ($allowedRoles as $role) {
            $role = trim($role);
            if (AccountType::tryFrom($role) === $userAccountType) {
                return $next($request);
            }
        }

        // User doesn't have required role
        return $this->unauthorizedResponse(
            $request,
            'Insufficient permissions. Required role: '.implode(' or ', $allowedRoles)
        );
    }

    /**
     * Generate appropriate unauthorized response based on request type.
     */
    private function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'error' => 'Unauthorized',
            ], 403);
        }

        // For web requests, redirect back with error
        return redirect()->back()->with('error', $message);
    }
}
