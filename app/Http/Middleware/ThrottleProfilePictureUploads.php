<?php

namespace App\Http\Middleware;

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleProfilePictureUploads
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->ensureIsNotRateLimited($request);

        RateLimiter::hit($this->throttleKey($request), 3600); // 60 minutes

        return $next($request);
    }

    /**
     * Ensure the request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $maxAttempts = 10;

        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            return;
        }

        SecurityEventLog::createEvent(
            SecurityEventType::PROFILE_PICTURE_UPLOAD_FAILED,
            user: $request->user(),
            metadata: [
                'failure_reason' => 'rate_limited',
                'max_attempts' => $maxAttempts,
            ]
        );

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        abort(429, 'Too many upload attempts. Please try again in '.ceil($seconds / 60).' minutes.');
    }

    /**
     * Get the rate limiting throttle key.
     */
    protected function throttleKey(Request $request): string
    {
        return 'profile-picture-upload:'.$request->user()->id.'|'.$request->ip();
    }
}
