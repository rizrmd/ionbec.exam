<?php

namespace App\Services;

use App\Models\Deliveries\Delivery;
use App\Models\Attempts\Attempt;
use App\Models\Client;
use Carbon\Carbon;

class ExamTimerService
{
    /**
     * Calculate remaining seconds for an exam delivery
     * Unified method for all timer calculations
     */
    public function calculateRemainingSeconds(Delivery $delivery, Attempt $attempt = null): int
    {
        $window = $this->window($delivery, $attempt);
        $remainingSeconds = $window['expires_at']
            ? max(0, $window['server_now']->diffInSeconds($window['expires_at'], false))
            : 0;

        \Log::info('ExamTimerService: Calculated remaining time', [
            'delivery_id' => $delivery->id,
            'attempt_id' => $attempt->id ?? null,
            'start_time' => $window['start_at']?->toDateTimeString(),
            'end_time' => $window['expires_at']?->toDateTimeString(),
            'remaining_seconds' => $remainingSeconds,
            'remaining_minutes' => round($remainingSeconds / 60, 2)
        ]);

        return $remainingSeconds;
    }
    
    /**
     * Determine the exam start time based on delivery type and attempt
     */
    public function window(Delivery $delivery, Attempt $attempt = null): array
    {
        $duration = max(0, (int) $delivery->duration) + max(0, (int) ($attempt->extra_minute ?? 0));
        $timezone = $this->timezone($delivery);
        $startAt = $this->determineStartTime($delivery, $attempt, $timezone);
        $expiresAt = $startAt ? $startAt->copy()->addMinutes($duration) : null;
        $serverNow = Carbon::now($timezone);

        return [
            'start_at' => $startAt,
            'expires_at' => $expiresAt,
            'server_now' => $serverNow,
            'duration_minutes' => $duration,
            'expired' => $expiresAt ? $serverNow->greaterThanOrEqualTo($expiresAt) : true,
        ];
    }

    public function expiresAt(Attempt $attempt): Carbon
    {
        $window = $this->window($attempt->delivery, $attempt);

        return $window['expires_at'] ?? Carbon::now();
    }

    private function determineStartTime(Delivery $delivery, Attempt $attempt = null, ?string $timezone = null): ?Carbon
    {
        $timezone ??= $this->timezone($delivery);

        // Strict scheduled window: automatic exams begin for everyone at scheduled_at.
        if ($delivery->automatic_start) {
            $scheduledTime = $this->parseLocalDateTime($delivery->getRawOriginal('scheduled_at') ?: $delivery->scheduled_at, $timezone);

            // If no scheduled time, return null to handle gracefully
            if (!$scheduledTime) {
                \Log::warning('ExamTimerService: No scheduled time available for automatic start', [
                    'delivery_id' => $delivery->id,
                    'automatic_start' => $delivery->automatic_start
                ]);
                return null;
            }

            \Log::info('ExamTimerService: Using scheduled time for automatic start', [
                'delivery_id' => $delivery->id,
                'scheduled_at' => $scheduledTime->toDateTimeString(),
                'automatic_start' => $delivery->automatic_start
            ]);

            return $scheduledTime;
        }

        if ($attempt && $attempt->started_at) {
            return $this->parseLocalDateTime($attempt->getRawOriginal('started_at') ?: $attempt->started_at, $timezone);
        }

        return null;
    }

    private function parseLocalDateTime($value, string $timezone): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->copy()->timezone($timezone);
        }

        return Carbon::parse((string) $value, $timezone);
    }

    private function timezone(Delivery $delivery): string
    {
        $client = Client::withoutGlobalScopes()->find($delivery->client_id);

        return $client->settings['time_zone'] ?? config('app.timezone', 'Asia/Jakarta');
    }
    
    /**
     * Check if an attempt has expired
     */
    public function isAttemptExpired(Attempt $attempt): bool
    {
        return $this->window($attempt->delivery, $attempt)['expired'];
    }
}
