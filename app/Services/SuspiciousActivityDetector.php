<?php

namespace App\Services;

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SuspiciousActivityDetector
{
    protected int $maxFailedAttempts = 5;
    protected int $timeWindowMinutes = 15;
    protected int $unusualActivityThreshold = 10;

    /**
     * Analyze recent activity for a user and detect suspicious patterns.
     */
    public function analyzeUserActivity(User $user): array
    {
        $recentEvents = $this->getRecentUserEvents($user);
        $suspiciousPatterns = [];

        // Check for multiple failed login attempts
        $failedLogins = $this->checkFailedLoginAttempts($recentEvents);
        if ($failedLogins['is_suspicious']) {
            $suspiciousPatterns[] = $failedLogins;
        }

        // Check for unusual activity volume
        $activityVolume = $this->checkActivityVolume($recentEvents);
        if ($activityVolume['is_suspicious']) {
            $suspiciousPatterns[] = $activityVolume;
        }

        // Check for multiple IP addresses
        $multipleIPs = $this->checkMultipleIPAddresses($recentEvents);
        if ($multipleIPs['is_suspicious']) {
            $suspiciousPatterns[] = $multipleIPs;
        }

        // Check for off-hours activity
        $offHours = $this->checkOffHoursActivity($recentEvents);
        if ($offHours['is_suspicious']) {
            $suspiciousPatterns[] = $offHours;
        }

        return $suspiciousPatterns;
    }

    /**
     * Analyze activity for a specific IP address.
     */
    public function analyzeIPActivity(string $ipAddress): array
    {
        $recentEvents = $this->getRecentIPEvents($ipAddress);
        $suspiciousPatterns = [];

        // Check for multiple user attempts from same IP
        $multipleUsers = $this->checkMultipleUserAttempts($recentEvents);
        if ($multipleUsers['is_suspicious']) {
            $suspiciousPatterns[] = $multipleUsers;
        }

        // Check for high failure rate
        $failureRate = $this->checkHighFailureRate($recentEvents);
        if ($failureRate['is_suspicious']) {
            $suspiciousPatterns[] = $failureRate;
        }

        return $suspiciousPatterns;
    }

    /**
     * Get recent security events for a user.
     */
    protected function getRecentUserEvents(User $user): Collection
    {
        return SecurityEventLog::forUser($user)
            ->recent($this->timeWindowMinutes * 4) // Extended window for pattern analysis
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Get recent security events for an IP address.
     */
    protected function getRecentIPEvents(string $ipAddress): Collection
    {
        return SecurityEventLog::where('ip_address', $ipAddress)
            ->recent($this->timeWindowMinutes * 4)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Check for multiple failed login attempts.
     */
    protected function checkFailedLoginAttempts(Collection $events): array
    {
        $failedLogins = $events->filter(function ($event) {
            return $event->event_type === SecurityEventType::LOGIN_FAILED;
        });

        $recentFailed = $failedLogins->filter(function ($event) {
            return $event->occurred_at >= Carbon::now()->subMinutes($this->timeWindowMinutes);
        });

        $is_suspicious = $recentFailed->count() >= $this->maxFailedAttempts;

        return [
            'type' => 'multiple_failed_logins',
            'is_suspicious' => $is_suspicious,
            'severity' => $is_suspicious ? 'high' : 'low',
            'details' => [
                'failed_attempts' => $recentFailed->count(),
                'threshold' => $this->maxFailedAttempts,
                'time_window' => $this->timeWindowMinutes,
                'ip_addresses' => $recentFailed->pluck('ip_address')->unique()->values(),
            ],
        ];
    }

    /**
     * Check for unusual activity volume.
     */
    protected function checkActivityVolume(Collection $events): array
    {
        $recentEvents = $events->filter(function ($event) {
            return $event->occurred_at >= Carbon::now()->subMinutes($this->timeWindowMinutes);
        });

        $is_suspicious = $recentEvents->count() >= $this->unusualActivityThreshold;

        return [
            'type' => 'high_activity_volume',
            'is_suspicious' => $is_suspicious,
            'severity' => $is_suspicious ? 'medium' : 'low',
            'details' => [
                'event_count' => $recentEvents->count(),
                'threshold' => $this->unusualActivityThreshold,
                'time_window' => $this->timeWindowMinutes,
                'event_types' => $recentEvents->groupBy('event_type')->map->count(),
            ],
        ];
    }

    /**
     * Check for multiple IP addresses.
     */
    protected function checkMultipleIPAddresses(Collection $events): array
    {
        $recentEvents = $events->filter(function ($event) {
            return $event->occurred_at >= Carbon::now()->subHours(2); // Wider window for IP analysis
        });

        $uniqueIPs = $recentEvents->pluck('ip_address')->unique();
        $is_suspicious = $uniqueIPs->count() >= 3;

        return [
            'type' => 'multiple_ip_addresses',
            'is_suspicious' => $is_suspicious,
            'severity' => $is_suspicious ? 'high' : 'low',
            'details' => [
                'ip_count' => $uniqueIPs->count(),
                'ip_addresses' => $uniqueIPs->values(),
                'threshold' => 3,
                'time_window' => 120, // 2 hours
            ],
        ];
    }

    /**
     * Check for off-hours activity.
     */
    protected function checkOffHoursActivity(Collection $events): array
    {
        $offHoursEvents = $events->filter(function ($event) {
            $hour = $event->occurred_at->hour;
            return $hour < 6 || $hour > 22; // Activity between 10 PM and 6 AM
        });

        $recentOffHours = $offHoursEvents->filter(function ($event) {
            return $event->occurred_at >= Carbon::now()->subHours(8);
        });

        $is_suspicious = $recentOffHours->count() >= 3;

        return [
            'type' => 'off_hours_activity',
            'is_suspicious' => $is_suspicious,
            'severity' => $is_suspicious ? 'medium' : 'low',
            'details' => [
                'off_hours_events' => $recentOffHours->count(),
                'threshold' => 3,
                'time_window' => 480, // 8 hours
                'events' => $recentOffHours->map(function ($event) {
                    return [
                        'type' => $event->event_type->value,
                        'time' => $event->occurred_at->format('H:i'),
                        'ip' => $event->ip_address,
                    ];
                }),
            ],
        ];
    }

    /**
     * Check for multiple user attempts from same IP.
     */
    protected function checkMultipleUserAttempts(Collection $events): array
    {
        $recentEvents = $events->filter(function ($event) {
            return $event->occurred_at >= Carbon::now()->subMinutes($this->timeWindowMinutes);
        });

        $uniqueUsers = $recentEvents->whereNotNull('user_id')->pluck('user_id')->unique();
        $uniqueEmails = $recentEvents->whereNotNull('email')->pluck('email')->unique();
        $totalUniqueIdentities = $uniqueUsers->merge($uniqueEmails)->unique()->count();

        $is_suspicious = $totalUniqueIdentities >= 5;

        return [
            'type' => 'multiple_user_attempts',
            'is_suspicious' => $is_suspicious,
            'severity' => $is_suspicious ? 'high' : 'low',
            'details' => [
                'unique_identities' => $totalUniqueIdentities,
                'threshold' => 5,
                'time_window' => $this->timeWindowMinutes,
                'event_count' => $recentEvents->count(),
            ],
        ];
    }

    /**
     * Check for high failure rate from IP.
     */
    protected function checkHighFailureRate(Collection $events): array
    {
        $recentEvents = $events->filter(function ($event) {
            return $event->occurred_at >= Carbon::now()->subMinutes($this->timeWindowMinutes);
        });

        $failedEvents = $recentEvents->filter(function ($event) {
            return in_array($event->event_type, [
                SecurityEventType::LOGIN_FAILED,
                SecurityEventType::REGISTRATION_FAILED,
                SecurityEventType::PASSWORD_RESET_FAILED,
                SecurityEventType::PASSWORD_CHANGE_FAILED,
            ]);
        });

        $totalEvents = $recentEvents->count();
        $failureRate = $totalEvents > 0 ? ($failedEvents->count() / $totalEvents) : 0;
        $is_suspicious = $failureRate >= 0.7 && $totalEvents >= 5;

        return [
            'type' => 'high_failure_rate',
            'is_suspicious' => $is_suspicious,
            'severity' => $is_suspicious ? 'high' : 'low',
            'details' => [
                'failure_rate' => round($failureRate * 100, 2),
                'threshold' => 70,
                'failed_events' => $failedEvents->count(),
                'total_events' => $totalEvents,
                'time_window' => $this->timeWindowMinutes,
            ],
        ];
    }

    /**
     * Log detected suspicious activity.
     */
    public function logSuspiciousActivity(?User $user, string $ipAddress, array $patterns): void
    {
        $highSeverityPatterns = array_filter($patterns, fn($p) => $p['severity'] === 'high');

        if (!empty($highSeverityPatterns)) {
            SecurityEventLog::createEvent(
                SecurityEventType::SUSPICIOUS_ACTIVITY,
                $user,
                ipAddress: $ipAddress,
                metadata: [
                    'detected_patterns' => $patterns,
                    'analysis_timestamp' => now(),
                    'pattern_count' => count($patterns),
                    'high_severity_count' => count($highSeverityPatterns),
                ]
            );
        }
    }

    /**
     * Check if IP should be temporarily blocked.
     */
    public function shouldBlockIP(string $ipAddress): bool
    {
        $cacheKey = "suspicious_ip_block:{$ipAddress}";

        return Cache::remember($cacheKey, 300, function () use ($ipAddress) {
            $patterns = $this->analyzeIPActivity($ipAddress);
            $highSeverityPatterns = array_filter($patterns, fn($p) => $p['severity'] === 'high');

            return count($highSeverityPatterns) >= 2;
        });
    }

    /**
     * Get security score for a user (0-100, higher is more suspicious).
     */
    public function getUserSecurityScore(User $user): int
    {
        $patterns = $this->analyzeUserActivity($user);

        $score = 0;
        foreach ($patterns as $pattern) {
            if ($pattern['is_suspicious']) {
                $score += match ($pattern['severity']) {
                    'critical' => 40,
                    'high' => 30,
                    'medium' => 20,
                    'low' => 10,
                    default => 5,
                };
            }
        }

        return min($score, 100);
    }
}
