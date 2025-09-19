<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RateLimitBypassService
{
    /**
     * Check if the current user should bypass rate limiting.
     */
    public function shouldBypass(Request $request): bool
    {
        // Check if bypass is enabled
        if (! config('auth.rate_limit_monitoring.admin_bypass.enabled', true)) {
            return false;
        }

        // Check if user is authenticated
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $allowedRoles = config('auth.rate_limit_monitoring.admin_bypass.roles', ['super-admin', 'admin']);

        // Check if user has an allowed role
        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                $this->logBypass($user, $request);

                return true;
            }
        }

        return false;
    }

    /**
     * Check if a specific user should bypass rate limiting.
     */
    public function userShouldBypass($user): bool
    {
        if (! $user || ! config('auth.rate_limit_monitoring.admin_bypass.enabled', true)) {
            return false;
        }

        $allowedRoles = config('auth.rate_limit_monitoring.admin_bypass.roles', ['super-admin', 'admin']);

        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log when an admin bypasses rate limiting.
     */
    protected function logBypass($user, Request $request): void
    {
        if (! config('auth.rate_limit_monitoring.admin_bypass.log_bypasses', true)) {
            return;
        }

        logger()->info('Rate limit bypass used', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()->getName(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get bypass configuration.
     */
    public function getBypassConfig(): array
    {
        return config('auth.rate_limit_monitoring.admin_bypass', [
            'enabled' => true,
            'roles' => ['super-admin', 'admin'],
            'log_bypasses' => true,
        ]);
    }

    /**
     * Check if user can reset rate limits for others.
     */
    public function canResetRateLimits($user): bool
    {
        if (! $user) {
            return false;
        }

        $allowedRoles = config('auth.rate_limit_monitoring.admin_bypass.roles', ['super-admin', 'admin']);

        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log rate limit reset action.
     */
    public function logRateLimitReset($admin, string $key, string $reason, Request $request): void
    {
        logger()->info('Rate limit reset by admin', [
            'admin_user_id' => $admin->id,
            'admin_email' => $admin->email,
            'rate_limit_key' => $key,
            'reason' => $reason,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);
    }
}
