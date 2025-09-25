<?php

namespace App\Models;

use App\Enums\SecurityEventType;
use App\Services\GeolocationService;
use App\Services\SecurityAlertService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SecurityEventLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_type',
        'severity',
        'description',
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'latitude',
        'longitude',
        'metadata',
        'session_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => SecurityEventType::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the user that triggered this security event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new security event log entry.
     */
    public static function createEvent(
        SecurityEventType $eventType,
        ?User $user = null,
        ?string $email = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $metadata = null,
        ?string $sessionId = null
    ): self {
        $ip = $ipAddress ?? request()->ip();
        $geolocationService = app(GeolocationService::class);
        $locationData = $geolocationService->getLocationData($ip);

        // Check for suspicious location if user exists
        if ($user && $eventType === SecurityEventType::LOGIN_SUCCESS) {
            $lastEvent = self::forUser($user)->recent(168)->first(); // Last week
            if ($lastEvent && $geolocationService->isLocationSuspicious($ip, $lastEvent->country)) {
                // Log unusual location event
                self::create([
                    'event_type' => SecurityEventType::UNUSUAL_LOCATION,
                    'severity' => SecurityEventType::UNUSUAL_LOCATION->getSeverity(),
                    'description' => SecurityEventType::UNUSUAL_LOCATION->getDescription(),
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent ?? request()->userAgent(),
                    'country' => $locationData['country'],
                    'city' => $locationData['city'],
                    'latitude' => $locationData['latitude'],
                    'longitude' => $locationData['longitude'],
                    'metadata' => array_merge($metadata ?? [], [
                        'previous_country' => $lastEvent->country,
                        'location_summary' => $geolocationService->getLocationSummary($ip),
                    ]),
                    'session_id' => $sessionId ?? session()->getId(),
                    'occurred_at' => now(),
                ]);
            }
        }

        $event = self::create([
            'event_type' => $eventType,
            'severity' => $eventType->getSeverity(),
            'description' => $eventType->getDescription(),
            'user_id' => $user?->id,
            'email' => $email ?? $user?->email,
            'ip_address' => $ip,
            'user_agent' => $userAgent ?? request()->userAgent(),
            'country' => $locationData['country'],
            'city' => $locationData['city'],
            'latitude' => $locationData['latitude'],
            'longitude' => $locationData['longitude'],
            'metadata' => $metadata,
            'session_id' => $sessionId ?? session()->getId(),
            'occurred_at' => now(),
        ]);

        // Trigger security alerts if necessary
        if ($event->shouldAlert()) {
            try {
                app(SecurityAlertService::class)->processSecurityEvent($event);
            } catch (\Exception $e) {
                // Don't let alerting failures prevent event logging
                \Log::error('Security alert processing failed', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $event;
    }

    /**
     * Log a successful login event.
     */
    public static function logLoginSuccess(User $user, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::LOGIN_SUCCESS,
            $user,
            metadata: $metadata
        );
    }

    /**
     * Log a failed login attempt.
     */
    public static function logLoginFailed(?string $email = null, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::LOGIN_FAILED,
            email: $email,
            metadata: $metadata
        );
    }

    /**
     * Log a rate-limited login attempt.
     */
    public static function logLoginRateLimited(?string $email = null, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::LOGIN_RATE_LIMITED,
            email: $email,
            metadata: $metadata
        );
    }

    /**
     * Log a successful registration.
     */
    public static function logRegistrationSuccess(User $user, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::REGISTRATION_SUCCESS,
            $user,
            metadata: $metadata
        );
    }

    /**
     * Log a failed registration attempt.
     */
    public static function logRegistrationFailed(?string $email = null, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::REGISTRATION_FAILED,
            email: $email,
            metadata: $metadata
        );
    }

    /**
     * Log a password reset request.
     */
    public static function logPasswordResetRequested(?string $email = null, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::PASSWORD_RESET_REQUESTED,
            email: $email,
            metadata: $metadata
        );
    }

    /**
     * Log a successful password reset.
     */
    public static function logPasswordResetSuccess(User $user, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::PASSWORD_RESET_SUCCESS,
            $user,
            metadata: $metadata
        );
    }

    /**
     * Log a logout event.
     */
    public static function logLogout(User $user, ?array $metadata = null): self
    {
        return self::createEvent(
            SecurityEventType::LOGOUT,
            $user,
            metadata: $metadata
        );
    }

    /**
     * Check if this event should trigger an alert.
     */
    public function shouldAlert(): bool
    {
        return $this->event_type->shouldAlert();
    }

    /**
     * Scope to get events for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
    }

    /**
     * Scope to get events by severity level.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope to get recent events.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Scope to get events from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('occurred_at', today());
    }

    /**
     * Scope to get events by IP address.
     */
    public function scopeByIpAddress($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope to get events by user ID.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get events that should trigger alerts.
     */
    public function scopeAlertsOnly($query)
    {
        return $query->whereIn('severity', ['alert', 'critical']);
    }
}
