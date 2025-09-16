<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$statuses): Response
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return $this->unauthorizedResponse($request, 'Authentication required.');
        }

        $user = Auth::user();

        // Default to requiring Active status if no statuses specified
        if (empty($statuses)) {
            $statuses = [AccountStatus::Active->value];
        }

        // Parse comma-separated statuses from single parameter if needed
        $allowedStatuses = [];
        foreach ($statuses as $status) {
            $allowedStatuses = array_merge($allowedStatuses, explode(',', $status));
        }

        // Check if user has required account status
        $userAccountStatus = $user->account_status;
        foreach ($allowedStatuses as $status) {
            $status = trim($status);
            if (AccountStatus::tryFrom($status) === $userAccountStatus) {
                return $next($request);
            }
        }

        // User doesn't have required status
        $message = $this->getStatusMessage($user->account_status);

        return $this->unauthorizedResponse($request, $message);
    }

    /**
     * Generate appropriate unauthorized response based on request type.
     */
    private function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'error' => 'Account Status Error',
            ], 403);
        }

        // For web requests, redirect back with error
        return redirect()->back()->with('error', $message);
    }

    /**
     * Get user-friendly message based on account status.
     */
    private function getStatusMessage(AccountStatus $status): string
    {
        return match ($status) {
            AccountStatus::Pending => 'Your account is pending approval. Please wait for administrator approval.',
            AccountStatus::Suspended => 'Your account has been suspended. Please contact support.',
            AccountStatus::Deleted => 'Your account has been deactivated.',
            default => 'Your account status does not allow access to this resource.',
        };
    }
}
