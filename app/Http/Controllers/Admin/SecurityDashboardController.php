<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEventLog;
use App\Services\SuspiciousActivityDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class SecurityDashboardController extends Controller
{
    public function __construct(
        protected SuspiciousActivityDetector $suspiciousActivityDetector
    ) {}

    /**
     * Display the security dashboard overview.
     */
    public function index(): Response
    {
        $stats = $this->getSecurityStats();
        $recentEvents = $this->getRecentSecurityEvents();
        $topThreats = $this->getTopThreats();
        $alertsOverTime = $this->getAlertsOverTime();

        return Inertia::render('Admin/Security/Dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'topThreats' => $topThreats,
            'alertsOverTime' => $alertsOverTime,
        ]);
    }

    /**
     * Display detailed security events.
     */
    public function events(Request $request): Response
    {
        $query = SecurityEventLog::with('user')
            ->orderBy('occurred_at', 'desc');

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%'.$request->ip_address.'%');
        }

        $events = $query->paginate(50)->withQueryString();

        return Inertia::render('Admin/Security/Events', [
            'events' => $events,
            'filters' => $request->only(['event_type', 'severity', 'date_from', 'date_to', 'ip_address']),
            'eventTypes' => $this->getEventTypes(),
            'severityLevels' => ['info', 'notice', 'warning', 'alert', 'critical'],
        ]);
    }

    /**
     * Display suspicious activity analysis.
     */
    public function suspicious(): Response
    {
        $suspiciousIPs = $this->getSuspiciousIPs();
        $suspiciousUsers = $this->getSuspiciousUsers();
        $blockedIPs = $this->getBlockedIPs();

        return Inertia::render('Admin/Security/Suspicious', [
            'suspiciousIPs' => $suspiciousIPs,
            'suspiciousUsers' => $suspiciousUsers,
            'blockedIPs' => $blockedIPs,
        ]);
    }

    /**
     * Get security statistics.
     */
    protected function getSecurityStats(): array
    {
        $last24Hours = Carbon::now()->subDay();
        $last7Days = Carbon::now()->subWeek();

        return [
            'total_events_24h' => SecurityEventLog::where('occurred_at', '>=', $last24Hours)->count(),
            'failed_logins_24h' => SecurityEventLog::where('event_type', 'login_failed')
                ->where('occurred_at', '>=', $last24Hours)->count(),
            'suspicious_events_24h' => SecurityEventLog::where('severity', 'alert')
                ->orWhere('severity', 'critical')
                ->where('occurred_at', '>=', $last24Hours)->count(),
            'unique_ips_24h' => SecurityEventLog::where('occurred_at', '>=', $last24Hours)
                ->distinct('ip_address')->count('ip_address'),
            'total_events_7d' => SecurityEventLog::where('occurred_at', '>=', $last7Days)->count(),
            'failed_logins_7d' => SecurityEventLog::where('event_type', 'login_failed')
                ->where('occurred_at', '>=', $last7Days)->count(),
            'suspicious_events_7d' => SecurityEventLog::where('severity', 'alert')
                ->orWhere('severity', 'critical')
                ->where('occurred_at', '>=', $last7Days)->count(),
            'unique_ips_7d' => SecurityEventLog::where('occurred_at', '>=', $last7Days)
                ->distinct('ip_address')->count('ip_address'),
        ];
    }

    /**
     * Get recent security events.
     */
    protected function getRecentSecurityEvents(): array
    {
        return SecurityEventLog::with('user')
            ->orderBy('occurred_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type->value,
                    'description' => $event->description,
                    'severity' => $event->severity,
                    'user' => $event->user ? [
                        'id' => $event->user->id,
                        'name' => $event->user->name,
                        'email' => $event->user->email,
                    ] : null,
                    'email' => $event->email,
                    'ip_address' => $event->ip_address,
                    'country' => $event->country,
                    'city' => $event->city,
                    'occurred_at' => $event->occurred_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get top threats (IPs with most suspicious activity).
     */
    protected function getTopThreats(): array
    {
        return SecurityEventLog::selectRaw('ip_address, country, city, COUNT(*) as event_count')
            ->whereIn('severity', ['alert', 'critical'])
            ->where('occurred_at', '>=', Carbon::now()->subWeek())
            ->groupBy('ip_address', 'country', 'city')
            ->orderBy('event_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($threat) {
                return [
                    'ip_address' => $threat->ip_address,
                    'location' => $threat->city && $threat->country
                        ? "{$threat->city}, {$threat->country}"
                        : ($threat->country ?: 'Unknown'),
                    'event_count' => $threat->event_count,
                    'is_blocked' => $this->suspiciousActivityDetector->shouldBlockIP($threat->ip_address),
                ];
            })
            ->toArray();
    }

    /**
     * Get alerts over time for chart.
     */
    protected function getAlertsOverTime(): array
    {
        $last7Days = Carbon::now()->subDays(7);

        $alerts = SecurityEventLog::selectRaw('DATE(occurred_at) as date, COUNT(*) as count')
            ->whereIn('severity', ['alert', 'critical'])
            ->where('occurred_at', '>=', $last7Days)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with 0 count
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $count = $alerts->firstWhere('date', $date)?->count ?? 0;
            $result[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        return $result;
    }

    /**
     * Get suspicious IP addresses.
     */
    protected function getSuspiciousIPs(): array
    {
        $suspiciousIPs = SecurityEventLog::selectRaw('ip_address, country, city, MAX(occurred_at) as last_activity')
            ->whereIn('severity', ['alert', 'critical'])
            ->where('occurred_at', '>=', Carbon::now()->subDay())
            ->groupBy('ip_address', 'country', 'city')
            ->orderBy('last_activity', 'desc')
            ->limit(20)
            ->get();

        return $suspiciousIPs->map(function ($ip) {
            $patterns = $this->suspiciousActivityDetector->analyzeIPActivity($ip->ip_address);

            return [
                'ip_address' => $ip->ip_address,
                'location' => $ip->city && $ip->country
                    ? "{$ip->city}, {$ip->country}"
                    : ($ip->country ?: 'Unknown'),
                'last_activity' => $ip->last_activity,
                'threat_level' => $this->calculateThreatLevel($patterns),
                'patterns' => $patterns,
                'should_block' => $this->suspiciousActivityDetector->shouldBlockIP($ip->ip_address),
            ];
        })->toArray();
    }

    /**
     * Get suspicious users.
     */
    protected function getSuspiciousUsers(): array
    {
        $suspiciousUsers = SecurityEventLog::with('user')
            ->selectRaw('user_id, MAX(occurred_at) as last_activity')
            ->whereNotNull('user_id')
            ->whereIn('severity', ['alert', 'critical'])
            ->where('occurred_at', '>=', Carbon::now()->subDay())
            ->groupBy('user_id')
            ->orderBy('last_activity', 'desc')
            ->limit(20)
            ->get();

        return $suspiciousUsers->map(function ($record) {
            if (! $record->user) {
                return null;
            }

            $patterns = $this->suspiciousActivityDetector->analyzeUserActivity($record->user);
            $securityScore = $this->suspiciousActivityDetector->getUserSecurityScore($record->user);

            return [
                'user' => [
                    'id' => $record->user->id,
                    'name' => $record->user->name,
                    'email' => $record->user->email,
                ],
                'last_activity' => $record->last_activity,
                'security_score' => $securityScore,
                'threat_level' => $this->calculateThreatLevel($patterns),
                'patterns' => $patterns,
            ];
        })->filter()->toArray();
    }

    /**
     * Get blocked IP addresses.
     */
    protected function getBlockedIPs(): array
    {
        // This would typically come from a cache or database of blocked IPs
        // For now, we'll get recently suspicious IPs that should be blocked
        return collect($this->getSuspiciousIPs())
            ->filter(fn ($ip) => $ip['should_block'])
            ->values()
            ->toArray();
    }

    /**
     * Get available event types.
     */
    protected function getEventTypes(): array
    {
        return SecurityEventLog::distinct('event_type')
            ->pluck('event_type')
            ->map(fn ($type) => ['value' => $type, 'label' => ucwords(str_replace('_', ' ', $type))])
            ->toArray();
    }

    /**
     * Calculate threat level based on patterns.
     */
    protected function calculateThreatLevel(array $patterns): string
    {
        if (empty($patterns)) {
            return 'low';
        }

        $highSeverityCount = count(array_filter($patterns, fn ($p) => $p['severity'] === 'high'));
        $mediumSeverityCount = count(array_filter($patterns, fn ($p) => $p['severity'] === 'medium'));

        if ($highSeverityCount >= 2) {
            return 'critical';
        }
        if ($highSeverityCount >= 1) {
            return 'high';
        }
        if ($mediumSeverityCount >= 2) {
            return 'medium';
        }

        return 'low';
    }
}
