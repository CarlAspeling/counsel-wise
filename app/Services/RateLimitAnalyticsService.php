<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RateLimitAnalyticsService
{
    /**
     * Get analytics data for a specified time period.
     */
    public function getAnalyticsData(string $period = '24h'): array
    {
        $startTime = $this->getStartTimeForPeriod($period);
        $endTime = now();

        return [
            'period' => $period,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_violations' => $this->getTotalViolations($startTime, $endTime),
            'violations_by_type' => $this->getViolationsByType($startTime, $endTime),
            'violations_by_hour' => $this->getViolationsByHour($startTime, $endTime),
            'top_violating_ips' => $this->getTopViolatingIPs($startTime, $endTime),
            'current_active_throttles' => $this->getCurrentActiveThrottles(),
            'effectiveness_metrics' => $this->getEffectivenessMetrics($startTime, $endTime),
        ];
    }

    /**
     * Get current rate limit statistics.
     */
    public function getCurrentStats(): array
    {
        $limits = config('auth.rate_limits', []);
        $stats = [];

        foreach ($limits as $type => $config) {
            $stats[$type] = [
                'max_attempts' => $config['max_attempts'],
                'decay_seconds' => $config['decay_seconds'],
                'current_active_violations' => $this->getCurrentActiveViolations($type),
                'total_attempts_today' => $this->getTotalAttemptsToday($type),
                'success_rate' => $this->getSuccessRate($type),
            ];
        }

        return $stats;
    }

    /**
     * Get rate limit trends over time.
     */
    public function getTrends(string $period = '7d'): array
    {
        $startTime = $this->getStartTimeForPeriod($period);
        $intervals = $this->getIntervals($period);

        $trends = [];
        foreach ($intervals as $interval) {
            $trends[] = [
                'timestamp' => $interval,
                'violations' => $this->getViolationsForInterval($interval),
                'attempts' => $this->getAttemptsForInterval($interval),
            ];
        }

        return $trends;
    }

    /**
     * Get rate limit effectiveness metrics.
     */
    public function getEffectivenessMetrics(Carbon $startTime, Carbon $endTime): array
    {
        return [
            'blocked_malicious_attempts' => $this->getBlockedMaliciousAttempts($startTime, $endTime),
            'legitimate_users_affected' => $this->getLegitimateUsersAffected($startTime, $endTime),
            'average_violation_duration' => $this->getAverageViolationDuration($startTime, $endTime),
            'repeat_offender_rate' => $this->getRepeatOffenderRate($startTime, $endTime),
        ];
    }

    /**
     * Export analytics data in specified format.
     */
    public function exportData(string $period, string $format = 'json'): array
    {
        $data = $this->getAnalyticsData($period);

        if ($format === 'csv') {
            return $this->formatForCsv($data);
        }

        return $data;
    }

    /**
     * Get start time for a given period.
     */
    protected function getStartTimeForPeriod(string $period): Carbon
    {
        return match ($period) {
            '1h' => now()->subHour(),
            '24h' => now()->subDay(),
            '7d' => now()->subWeek(),
            '30d' => now()->subMonth(),
            default => now()->subDay(),
        };
    }

    /**
     * Get total violations for time period.
     */
    protected function getTotalViolations(Carbon $startTime, Carbon $endTime): int
    {
        // This would typically query your rate limit violation log
        // For now, return a mock value
        return Cache::remember(
            "rate_limit_violations:{$startTime->timestamp}:{$endTime->timestamp}",
            300, // 5 minutes cache
            fn () => rand(50, 200)
        );
    }

    /**
     * Get violations broken down by type.
     */
    protected function getViolationsByType(Carbon $startTime, Carbon $endTime): array
    {
        $types = array_keys(config('auth.rate_limits', []));
        $violations = [];

        foreach ($types as $type) {
            $violations[$type] = $this->getViolationsForType($type, $startTime, $endTime);
        }

        return $violations;
    }

    /**
     * Get violations for a specific type.
     */
    protected function getViolationsForType(string $type, Carbon $startTime, Carbon $endTime): int
    {
        // This would query your logging system
        return Cache::remember(
            "rate_limit_violations:{$type}:{$startTime->timestamp}:{$endTime->timestamp}",
            300,
            fn () => rand(5, 50)
        );
    }

    /**
     * Get violations broken down by hour.
     */
    protected function getViolationsByHour(Carbon $startTime, Carbon $endTime): array
    {
        $hours = [];
        $current = $startTime->copy()->startOfHour();

        while ($current->lte($endTime)) {
            $hours[$current->format('Y-m-d H:00')] = rand(1, 20);
            $current->addHour();
        }

        return $hours;
    }

    /**
     * Get top violating IP addresses.
     */
    protected function getTopViolatingIPs(Carbon $startTime, Carbon $endTime): array
    {
        // This would query your logging system
        return [
            ['ip' => '192.168.1.100', 'violations' => 25, 'attempts' => 100],
            ['ip' => '10.0.0.50', 'violations' => 15, 'attempts' => 60],
            ['ip' => '172.16.0.10', 'violations' => 10, 'attempts' => 40],
        ];
    }

    /**
     * Get currently active throttles.
     */
    protected function getCurrentActiveThrottles(): array
    {
        // This would scan the cache/rate limiter store
        return [];
    }

    /**
     * Get current active violations for a type.
     */
    protected function getCurrentActiveViolations(string $type): int
    {
        // This would count current active rate limits for the type
        return 0;
    }

    /**
     * Get total attempts today for a type.
     */
    protected function getTotalAttemptsToday(string $type): int
    {
        // This would query your logging system
        return rand(100, 1000);
    }

    /**
     * Get success rate for a type.
     */
    protected function getSuccessRate(string $type): float
    {
        $total = $this->getTotalAttemptsToday($type);
        $violations = $this->getViolationsForType($type, now()->startOfDay(), now());

        if ($total === 0) {
            return 100.0;
        }

        return round((($total - $violations) / $total) * 100, 2);
    }

    /**
     * Get time intervals for trend analysis.
     */
    protected function getIntervals(string $period): array
    {
        $startTime = $this->getStartTimeForPeriod($period);
        $intervals = [];

        $step = match ($period) {
            '1h' => 5, // 5-minute intervals
            '24h' => 60, // 1-hour intervals
            '7d' => 1440, // 1-day intervals
            '30d' => 1440, // 1-day intervals
            default => 60,
        };

        $current = $startTime->copy();
        while ($current->lte(now())) {
            $intervals[] = $current->copy();
            $current->addMinutes($step);
        }

        return $intervals;
    }

    /**
     * Get violations for a specific time interval.
     */
    protected function getViolationsForInterval(Carbon $interval): int
    {
        return rand(0, 10);
    }

    /**
     * Get attempts for a specific time interval.
     */
    protected function getAttemptsForInterval(Carbon $interval): int
    {
        return rand(10, 100);
    }

    /**
     * Get blocked malicious attempts count.
     */
    protected function getBlockedMaliciousAttempts(Carbon $startTime, Carbon $endTime): int
    {
        return rand(50, 200);
    }

    /**
     * Get count of legitimate users affected by rate limiting.
     */
    protected function getLegitimateUsersAffected(Carbon $startTime, Carbon $endTime): int
    {
        return rand(1, 10);
    }

    /**
     * Get average duration of rate limit violations.
     */
    protected function getAverageViolationDuration(Carbon $startTime, Carbon $endTime): float
    {
        return rand(300, 900); // 5-15 minutes in seconds
    }

    /**
     * Get repeat offender rate.
     */
    protected function getRepeatOffenderRate(Carbon $startTime, Carbon $endTime): float
    {
        return rand(10, 30); // 10-30% repeat rate
    }

    /**
     * Format data for CSV export.
     */
    protected function formatForCsv(array $data): array
    {
        $csv = [];

        // Add summary row
        $csv[] = [
            'Type' => 'Summary',
            'Period' => $data['period'],
            'Total Violations' => $data['total_violations'],
            'Start Time' => $data['start_time']->toISOString(),
            'End Time' => $data['end_time']->toISOString(),
        ];

        // Add violations by type
        foreach ($data['violations_by_type'] as $type => $count) {
            $csv[] = [
                'Type' => $type,
                'Period' => $data['period'],
                'Violations' => $count,
                'Start Time' => $data['start_time']->toISOString(),
                'End Time' => $data['end_time']->toISOString(),
            ];
        }

        return $csv;
    }
}
