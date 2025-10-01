<?php

namespace App\Http\Middleware;

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleProfileUpdates
{
    /**
     * Handle an incoming request to throttle profile update attempts.
     *
     * Prevents abuse by limiting profile updates to:
     * - 10 attempts per 60 minutes per user+IP combination
     * - Automatic reset after time window expires
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only throttle PATCH requests (profile updates)
        if ($request->method() !== 'PATCH') {
            return $next($request);
        }

        $this->ensureIsNotRateLimited($request);

        // Increment rate limiter
        RateLimiter::hit($this->throttleKey($request), 3600); // 60 minutes decay

        return $next($request);
    }

    /**
     * Ensure the profile update request is not rate limited.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $maxAttempts = 10;

        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            return;
        }

        // Log the rate limiting event
        SecurityEventLog::createEvent(
            SecurityEventType::PROFILE_UPDATE_RATE_LIMITED,
            user: $request->user(),
            metadata: [
                'max_attempts' => $maxAttempts,
                'throttle_key' => $this->throttleKey($request),
            ]
        );

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        abort(429, 'Too many profile update attempts. Please try again in '.ceil($seconds / 60).' minutes.');
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return 'profile-update:'.$request->user()->id.'|'.$request->ip();
    }
}
