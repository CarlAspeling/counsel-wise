<?php

namespace App\Http\Middleware;

use App\Models\PasswordChangeLog;
use Closure;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePasswordChanges
{
    /**
     * Handle an incoming request to throttle password change attempts.
     *
     * Prevents brute force attacks by limiting password change attempts to:
     * - 5 attempts per 15 minutes per user+IP combination
     * - Automatic reset after time window expires
     * - Manual clear on successful password change
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only throttle PUT/PATCH requests (password updates)
        if (! in_array($request->method(), ['PUT', 'PATCH'])) {
            return $next($request);
        }

        $this->ensureIsNotRateLimited($request);

        $response = $next($request);

        // Clear rate limit on successful password change
        if ($response->getStatusCode() === 302 &&
            $request->session()->get('status') === 'password-updated') {
            RateLimiter::clear($this->throttleKey($request));
        }

        return $response;
    }

    /**
     * Ensure the password change request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $maxAttempts = 5;
        $decayMinutes = 15;

        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            return;
        }

        // Log the rate limiting event for security monitoring
        $this->logRateLimitViolation($request);

        // Fire lockout event
        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'current_password' => [
                'Too many password change attempts. Please try again in '.
                ceil($seconds / 60).' minutes.',
            ],
        ])->errorBag('updatePassword');
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return 'password_change:'.$request->user()->id.'|'.$request->ip();
    }

    /**
     * Log rate limit violation for security monitoring.
     */
    protected function logRateLimitViolation(Request $request): void
    {
        PasswordChangeLog::create([
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'success' => false,
            'failure_reason' => 'rate_limited',
        ]);
    }

    /**
     * Increment rate limit attempts on validation failure.
     * This method can be called from the controller when validation fails.
     */
    public static function incrementAttempts(Request $request): void
    {
        $throttleKey = 'password_change:'.$request->user()->id.'|'.$request->ip();
        RateLimiter::hit($throttleKey, 900); // 15 minutes decay
    }
}
