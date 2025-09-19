<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\StatusCodes;
use App\Services\RateLimitAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class RateLimitController extends Controller
{
    /**
     * Display rate limiting dashboard overview.
     */
    public function index(Request $request)
    {
        if (! config('auth.rate_limit_monitoring.enabled', true)) {
            abort(404, 'Rate limit monitoring is disabled');
        }

        $stats = $this->getRateLimitStats();
        $activeThrottles = $this->getActiveThrottles();
        $recentViolations = $this->getRecentViolations();

        if ($request->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'active_throttles' => $activeThrottles,
                'recent_violations' => $recentViolations,
            ], StatusCodes::OK);
        }

        return Inertia::render('Admin/RateLimit/Dashboard', [
            'stats' => $stats,
            'activeThrottles' => $activeThrottles,
            'recentViolations' => $recentViolations,
            'refreshInterval' => config('auth.rate_limit_monitoring.dashboard_refresh_interval', 30),
        ]);
    }

    /**
     * Get current rate limiting statistics.
     */
    public function stats(Request $request)
    {
        $stats = $this->getRateLimitStats();

        return response()->json($stats, StatusCodes::OK);
    }

    /**
     * Get active throttled IPs/keys.
     */
    public function activeThrottles(Request $request)
    {
        $throttles = $this->getActiveThrottles();

        return response()->json($throttles, StatusCodes::OK);
    }

    /**
     * Reset rate limit for specific key.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $key = $request->input('key');
        $reason = $request->input('reason', 'Manual admin reset');

        // Clear the rate limit
        RateLimiter::clear($key);

        // Log the admin action
        $this->logAdminAction('rate_limit_reset', [
            'key' => $key,
            'reason' => $reason,
            'admin_user_id' => auth()->id(),
            'admin_ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Rate limit reset successfully',
                'key' => $key,
            ], StatusCodes::OK);
        }

        return redirect()->back()->with('success', "Rate limit reset for key: {$key}");
    }

    /**
     * Get analytics data for specified time period.
     */
    public function analytics(Request $request)
    {
        $request->validate([
            'period' => ['required', 'string', 'in:1h,24h,7d,30d'],
        ]);

        $period = $request->input('period');
        $analytics = $this->getAnalyticsData($period);

        return response()->json($analytics, StatusCodes::OK);
    }

    /**
     * Export rate limit data.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => ['required', 'string', 'in:csv,json'],
            'period' => ['required', 'string', 'in:1h,24h,7d,30d'],
        ]);

        $format = $request->input('format');
        $period = $request->input('period');

        $data = $this->getAnalyticsData($period);

        if ($format === 'csv') {
            return $this->exportAsCsv($data, $period);
        }

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=rate-limits-{$period}.json");
    }

    /**
     * Get current rate limiting statistics.
     */
    protected function getRateLimitStats(): array
    {
        $limits = config('auth.rate_limits', []);
        $stats = [];

        foreach ($limits as $type => $config) {
            $stats[$type] = [
                'max_attempts' => $config['max_attempts'],
                'decay_seconds' => $config['decay_seconds'],
                'current_violations' => $this->countCurrentViolations($type),
                'total_attempts_today' => $this->getTotalAttemptsToday($type),
            ];
        }

        return $stats;
    }

    /**
     * Get currently active throttled keys.
     */
    protected function getActiveThrottles(): array
    {
        $throttles = [];
        $cacheStore = Cache::getStore();

        // This is a simplified implementation - in production you might want
        // to store throttle information in a more queryable format
        $keyPatterns = [
            'login:*',
            'registration:*',
            'password-reset:*',
            'email-verification:*',
        ];

        foreach ($keyPatterns as $pattern) {
            $keys = $this->findThrottleKeys($pattern);
            foreach ($keys as $key) {
                $attempts = RateLimiter::attempts($key);
                $availableIn = RateLimiter::availableIn($key);

                if ($attempts > 0 && $availableIn > 0) {
                    $throttles[] = [
                        'key' => $key,
                        'type' => $this->extractTypeFromKey($key),
                        'attempts' => $attempts,
                        'available_in' => $availableIn,
                        'created_at' => now()->subSeconds($availableIn),
                    ];
                }
            }
        }

        return collect($throttles)->sortByDesc('attempts')->values()->all();
    }

    /**
     * Get recent rate limit violations.
     */
    protected function getRecentViolations(): array
    {
        // This would typically query a logging table
        // For now, return mock data structure
        return [
            [
                'id' => 1,
                'type' => 'login',
                'key' => 'login:test@example.com|192.168.1.1',
                'ip' => '192.168.1.1',
                'attempts' => 5,
                'created_at' => now()->subMinutes(10),
            ],
        ];
    }

    /**
     * Count current violations for a specific type.
     */
    protected function countCurrentViolations(string $type): int
    {
        // Implementation would depend on how you track violations
        return 0;
    }

    /**
     * Get total attempts today for a specific type.
     */
    protected function getTotalAttemptsToday(string $type): int
    {
        // Implementation would depend on your logging system
        return 0;
    }

    /**
     * Find throttle keys matching pattern.
     */
    protected function findThrottleKeys(string $pattern): array
    {
        // Simplified implementation - you might use Redis SCAN or similar
        return [];
    }

    /**
     * Extract rate limit type from cache key.
     */
    protected function extractTypeFromKey(string $key): string
    {
        if (str_starts_with($key, 'login:')) {
            return 'login';
        }

        if (str_starts_with($key, 'registration:')) {
            return 'registration';
        }

        if (str_starts_with($key, 'password-reset:')) {
            return 'password_reset';
        }

        if (str_starts_with($key, 'email-verification:')) {
            return 'email_verification';
        }

        return 'unknown';
    }

    /**
     * Get analytics data for specified period.
     */
    protected function getAnalyticsData(string $period): array
    {
        $analyticsService = app(RateLimitAnalyticsService::class);

        return $analyticsService->getAnalyticsData($period);
    }

    /**
     * Export data as CSV.
     */
    protected function exportAsCsv(array $data, string $period)
    {
        $filename = "rate-limits-{$period}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Type', 'Violations', 'Period']);

            foreach ($data['by_type'] as $type => $count) {
                fputcsv($file, [$type, $count, $data['period']]);
            }

            fclose($file);
        };

        return response()->stream($callback, StatusCodes::OK, $headers);
    }

    /**
     * Log administrative actions.
     */
    protected function logAdminAction(string $action, array $data): void
    {
        if (config('auth.rate_limit_monitoring.admin_bypass.log_bypasses', true)) {
            // Log to your preferred logging system
            logger()->info("Rate limit admin action: {$action}", $data);
        }
    }
}
