<?php

namespace App\Services;

use App\Enums\SecurityEventType;
use App\Models\SecurityEventLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SecurityAlertService
{
    protected array $criticalEvents = [
        SecurityEventType::SUSPICIOUS_ACTIVITY,
        SecurityEventType::MULTIPLE_LOGIN_ATTEMPTS,
        SecurityEventType::UNUSUAL_LOCATION,
        SecurityEventType::ACCOUNT_LOCKED,
        SecurityEventType::ACCOUNT_SUSPENDED,
    ];

    protected array $adminEmails = [];

    public function __construct()
    {
        $this->adminEmails = config('security.admin_emails', []);
    }

    /**
     * Process a security event and send alerts if necessary.
     */
    public function processSecurityEvent(SecurityEventLog $event): void
    {
        if (!$this->shouldAlert($event)) {
            return;
        }

        $this->logAlert($event);

        // Send different types of alerts based on severity
        if ($event->severity === 'critical') {
            $this->sendCriticalAlert($event);
        } elseif ($event->severity === 'alert') {
            $this->sendHighPriorityAlert($event);
        }

        // Check for patterns that warrant immediate attention
        $this->checkForCriticalPatterns($event);
    }

    /**
     * Determine if an event should trigger an alert.
     */
    protected function shouldAlert(SecurityEventLog $event): bool
    {
        return in_array($event->event_type, $this->criticalEvents) ||
               in_array($event->severity, ['alert', 'critical']);
    }

    /**
     * Log the alert to the application log.
     */
    protected function logAlert(SecurityEventLog $event): void
    {
        Log::channel('security')->warning('Security Alert Triggered', [
            'event_id' => $event->id,
            'event_type' => $event->event_type->value,
            'severity' => $event->severity,
            'user_id' => $event->user_id,
            'email' => $event->email,
            'ip_address' => $event->ip_address,
            'location' => $event->city && $event->country
                ? "{$event->city}, {$event->country}"
                : $event->country,
            'occurred_at' => $event->occurred_at,
            'metadata' => $event->metadata,
        ]);
    }

    /**
     * Send critical alert notifications.
     */
    protected function sendCriticalAlert(SecurityEventLog $event): void
    {
        $subject = "🚨 CRITICAL Security Alert - " . $event->event_type->getDescription();
        $message = $this->buildAlertMessage($event, 'CRITICAL');

        $this->sendEmailAlert($subject, $message, $event);
        $this->sendSlackAlert($message, $event, 'danger');
    }

    /**
     * Send high priority alert notifications.
     */
    protected function sendHighPriorityAlert(SecurityEventLog $event): void
    {
        $subject = "⚠️ High Priority Security Alert - " . $event->event_type->getDescription();
        $message = $this->buildAlertMessage($event, 'HIGH');

        $this->sendEmailAlert($subject, $message, $event);
    }

    /**
     * Build alert message content.
     */
    protected function buildAlertMessage(SecurityEventLog $event, string $priority): string
    {
        $location = $event->city && $event->country
            ? "{$event->city}, {$event->country}"
            : ($event->country ?: 'Unknown');

        $userInfo = $event->user
            ? "User: {$event->user->name} ({$event->user->email})"
            : ($event->email ? "Email: {$event->email}" : "Unknown user");

        return "
**{$priority} SECURITY ALERT**

Event: {$event->event_type->getDescription()}
Severity: {$event->severity}
Time: {$event->occurred_at->format('Y-m-d H:i:s T')}

{$userInfo}
IP Address: {$event->ip_address}
Location: {$location}

Description: {$event->description}

" . ($event->metadata ? "Additional Details: " . json_encode($event->metadata, JSON_PRETTY_PRINT) : '') . "

View full details: " . route('admin.security.events', ['id' => $event->id]);
    }

    /**
     * Send email alert to administrators.
     */
    protected function sendEmailAlert(string $subject, string $message, SecurityEventLog $event): void
    {
        if (empty($this->adminEmails)) {
            return;
        }

        try {
            foreach ($this->adminEmails as $email) {
                Mail::raw($message, function ($mail) use ($email, $subject) {
                    $mail->to($email)
                         ->subject($subject)
                         ->priority(1); // High priority
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to send security alert email', [
                'error' => $e->getMessage(),
                'event_id' => $event->id,
            ]);
        }
    }

    /**
     * Send Slack alert (placeholder for future implementation).
     */
    protected function sendSlackAlert(string $message, SecurityEventLog $event, string $color = 'warning'): void
    {
        // Placeholder for Slack webhook integration
        // This would typically use a Slack notification channel
        Log::info('Slack alert would be sent', [
            'message' => $message,
            'event_id' => $event->id,
            'color' => $color,
        ]);
    }

    /**
     * Check for critical patterns that warrant immediate attention.
     */
    protected function checkForCriticalPatterns(SecurityEventLog $event): void
    {
        // Check for multiple suspicious events from same IP in short time
        $this->checkMultipleSuspiciousEventsFromIP($event);

        // Check for unusual activity spikes
        $this->checkActivitySpikes($event);

        // Check for coordinated attacks
        $this->checkCoordinatedAttacks($event);
    }

    /**
     * Check for multiple suspicious events from the same IP.
     */
    protected function checkMultipleSuspiciousEventsFromIP(SecurityEventLog $event): void
    {
        $recentEvents = SecurityEventLog::where('ip_address', $event->ip_address)
            ->whereIn('severity', ['alert', 'critical'])
            ->where('occurred_at', '>=', Carbon::now()->subMinutes(15))
            ->count();

        if ($recentEvents >= 3) {
            $this->sendCriticalAlert($event);

            Log::channel('security')->critical('Multiple suspicious events from single IP', [
                'ip_address' => $event->ip_address,
                'event_count' => $recentEvents,
                'time_window' => '15 minutes',
            ]);
        }
    }

    /**
     * Check for unusual activity spikes.
     */
    protected function checkActivitySpikes(SecurityEventLog $event): void
    {
        $recentAlerts = SecurityEventLog::whereIn('severity', ['alert', 'critical'])
            ->where('occurred_at', '>=', Carbon::now()->subMinutes(10))
            ->count();

        if ($recentAlerts >= 10) {
            Log::channel('security')->critical('Unusual security activity spike detected', [
                'alert_count' => $recentAlerts,
                'time_window' => '10 minutes',
                'triggering_event_id' => $event->id,
            ]);
        }
    }

    /**
     * Check for coordinated attacks (multiple IPs, similar patterns).
     */
    protected function checkCoordinatedAttacks(SecurityEventLog $event): void
    {
        $recentSimilarEvents = SecurityEventLog::where('event_type', $event->event_type)
            ->where('occurred_at', '>=', Carbon::now()->subMinutes(30))
            ->distinct('ip_address')
            ->count('ip_address');

        if ($recentSimilarEvents >= 5) {
            Log::channel('security')->critical('Potential coordinated attack detected', [
                'event_type' => $event->event_type->value,
                'unique_ips' => $recentSimilarEvents,
                'time_window' => '30 minutes',
                'triggering_event_id' => $event->id,
            ]);
        }
    }

    /**
     * Generate security summary report for administrators.
     */
    public function generateDailySummary(): array
    {
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        $summary = [
            'period' => $yesterday->format('Y-m-d'),
            'total_events' => SecurityEventLog::whereBetween('occurred_at', [$yesterday, $today])->count(),
            'critical_events' => SecurityEventLog::where('severity', 'critical')
                ->whereBetween('occurred_at', [$yesterday, $today])->count(),
            'alert_events' => SecurityEventLog::where('severity', 'alert')
                ->whereBetween('occurred_at', [$yesterday, $today])->count(),
            'failed_logins' => SecurityEventLog::where('event_type', SecurityEventType::LOGIN_FAILED)
                ->whereBetween('occurred_at', [$yesterday, $today])->count(),
            'unique_ips' => SecurityEventLog::whereBetween('occurred_at', [$yesterday, $today])
                ->distinct('ip_address')->count('ip_address'),
            'top_event_types' => SecurityEventLog::selectRaw('event_type, COUNT(*) as count')
                ->whereBetween('occurred_at', [$yesterday, $today])
                ->groupBy('event_type')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
                ->toArray(),
        ];

        return $summary;
    }

    /**
     * Send daily security summary to administrators.
     */
    public function sendDailySummary(): void
    {
        $summary = $this->generateDailySummary();

        $subject = "Daily Security Summary - " . $summary['period'];
        $message = $this->buildSummaryMessage($summary);

        $this->sendEmailAlert($subject, $message, null);
    }

    /**
     * Build daily summary message.
     */
    protected function buildSummaryMessage(array $summary): string
    {
        $topEvents = collect($summary['top_event_types'])
            ->map(fn($event) => "- {$event['event_type']}: {$event['count']}")
            ->join("\n");

        return "
**DAILY SECURITY SUMMARY**
Period: {$summary['period']}

**Overview:**
- Total Events: {$summary['total_events']}
- Critical Events: {$summary['critical_events']}
- Alert Events: {$summary['alert_events']}
- Failed Logins: {$summary['failed_logins']}
- Unique IP Addresses: {$summary['unique_ips']}

**Top Event Types:**
{$topEvents}

View detailed reports: " . route('admin.security.dashboard');
    }
}
