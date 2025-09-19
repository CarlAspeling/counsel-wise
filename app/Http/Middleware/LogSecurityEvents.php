<?php

namespace App\Http\Middleware;

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogSecurityEvents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $user = Auth::user();

        try {
            $response = $next($request);

            // Log successful authentication events
            $this->logSuccessfulEvents($request, $response, $user);

            return $response;
        } catch (ValidationException $e) {
            // Log validation failures (which often indicate failed auth attempts)
            $this->logValidationErrors($request, $e, $user);
            throw $e;
        } catch (Throwable $e) {
            // Log other security-related exceptions
            $this->logSecurityExceptions($request, $e, $user);
            throw $e;
        }
    }

    /**
     * Log successful authentication events based on the route and response.
     */
    protected function logSuccessfulEvents(Request $request, Response $response, $user): void
    {
        $routeName = $request->route()?->getName();
        $statusCode = $response->getStatusCode();

        // Only log successful responses (2xx status codes)
        if ($statusCode < 200 || $statusCode >= 300) {
            return;
        }

        $metadata = [
            'route' => $routeName,
            'method' => $request->method(),
            'status_code' => $statusCode,
            'response_time_ms' => round((microtime(true) - $this->startTime ?? 0) * 1000, 2),
        ];

        match ($routeName) {
            'login' => $this->logIfUserChanged($user, SecurityEventType::LOGIN_SUCCESS, $metadata),
            'register' => SecurityEventLog::logRegistrationSuccess(Auth::user(), $metadata),
            'password.email' => SecurityEventLog::logPasswordResetRequested(
                $request->input('email'),
                $metadata
            ),
            'password.store' => SecurityEventLog::logPasswordResetSuccess(Auth::user(), $metadata),
            'verification.send' => $this->logEmailVerificationRequest($request, $metadata),
            'logout' => $this->logIfUserExists($user, SecurityEventType::LOGOUT, $metadata),
            default => null,
        };
    }

    /**
     * Log validation errors, particularly for authentication attempts.
     */
    protected function logValidationErrors(Request $request, ValidationException $e, $user): void
    {
        $routeName = $request->route()?->getName();
        $errors = $e->errors();

        $metadata = [
            'route' => $routeName,
            'method' => $request->method(),
            'validation_errors' => array_keys($errors),
            'error_count' => count($errors),
        ];

        // Check if this is a rate limiting error
        if ($this->isRateLimitError($e)) {
            $this->logRateLimitEvents($request, $routeName, $metadata);
            return;
        }

        // Log authentication failures
        match ($routeName) {
            'login' => SecurityEventLog::logLoginFailed(
                $request->input('email'),
                array_merge($metadata, ['errors' => $errors])
            ),
            'register' => SecurityEventLog::logRegistrationFailed(
                $request->input('email'),
                array_merge($metadata, ['errors' => $errors])
            ),
            default => null,
        };
    }

    /**
     * Log rate limiting events based on route.
     */
    protected function logRateLimitEvents(Request $request, ?string $routeName, array $metadata): void
    {
        match ($routeName) {
            'login' => SecurityEventLog::logLoginRateLimited(
                $request->input('email'),
                $metadata
            ),
            'password.email', 'password.store' => SecurityEventLog::createEvent(
                SecurityEventType::PASSWORD_RESET_RATE_LIMITED,
                email: $request->input('email'),
                metadata: $metadata
            ),
            'verification.send' => SecurityEventLog::createEvent(
                SecurityEventType::EMAIL_VERIFICATION_RATE_LIMITED,
                user: Auth::user(),
                metadata: $metadata
            ),
            default => null,
        };
    }

    /**
     * Log security-related exceptions.
     */
    protected function logSecurityExceptions(Request $request, Throwable $e, $user): void
    {
        // Only log certain types of security-related exceptions
        $securityExceptions = [
            'Illuminate\Auth\AuthenticationException',
            'Illuminate\Auth\Access\AuthorizationException',
            'Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException',
        ];

        if (!in_array(get_class($e), $securityExceptions)) {
            return;
        }

        $metadata = [
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'exception' => get_class($e),
            'message' => $e->getMessage(),
        ];

        SecurityEventLog::createEvent(
            SecurityEventType::SUSPICIOUS_ACTIVITY,
            user: $user,
            metadata: $metadata
        );
    }

    /**
     * Check if the validation exception is due to rate limiting.
     */
    protected function isRateLimitError(ValidationException $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'Too many') ||
               str_contains($message, 'throttle') ||
               str_contains($message, 'rate limit');
    }

    /**
     * Log event if user recently changed (for login success).
     */
    protected function logIfUserChanged($previousUser, SecurityEventType $eventType, array $metadata): void
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->id !== $previousUser?->id) {
            SecurityEventLog::createEvent($eventType, $currentUser, metadata: $metadata);
        }
    }

    /**
     * Log event if user exists.
     */
    protected function logIfUserExists($user, SecurityEventType $eventType, array $metadata): void
    {
        if ($user) {
            SecurityEventLog::createEvent($eventType, $user, metadata: $metadata);
        }
    }

    /**
     * Log email verification request.
     */
    protected function logEmailVerificationRequest(Request $request, array $metadata): void
    {
        SecurityEventLog::createEvent(
            SecurityEventType::EMAIL_VERIFICATION_REQUESTED,
            user: Auth::user(),
            metadata: $metadata
        );
    }
}
