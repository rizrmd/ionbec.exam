<?php

namespace App\Services;

use App\Models\Deliveries\Delivery;
use App\Models\Attempts\Attempt;
use Carbon\Carbon;

class ExamTimerService
{
    /**
     * Calculate remaining seconds for an exam delivery
     * Unified method for all timer calculations
     */
    public function calculateRemainingSeconds(Delivery $delivery, Attempt $attempt = null): int
    {
        $baseDuration = $delivery->duration + ($attempt->extra_minute ?? 0);

        // Determine the start time based on delivery and attempt
        $startTime = $this->determineStartTime($delivery, $attempt);

        if (!$startTime) {
            \Log::warning('ExamTimerService: No valid start time found', [
                'delivery_id' => $delivery->id,
                'attempt_id' => $attempt->id ?? null,
                'base_duration' => $baseDuration
            ]);
            return 0;
        }

        $endTime = $startTime->copy()->addMinutes($baseDuration);
        $remainingSeconds = max(0, $endTime->diffInSeconds(Carbon::now()));

        \Log::info('ExamTimerService: Calculated remaining time', [
            'delivery_id' => $delivery->id,
            'attempt_id' => $attempt->id ?? null,
            'base_duration' => $baseDuration,
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
            'remaining_seconds' => $remainingSeconds,
            'remaining_minutes' => round($remainingSeconds / 60, 2)
        ]);

        return $remainingSeconds;
    }
    
    /**
     * Determine the exam start time based on delivery type and attempt
     */
    private function determineStartTime(Delivery $delivery, Attempt $attempt = null): ?Carbon
    {
        // For automatic_start, always use scheduled time
        if ($delivery->automatic_start) {
            $scheduledTime = $delivery->scheduled_at ? Carbon::parse($delivery->scheduled_at) : null;

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

        // For manual start, use attempt start time if available
        if ($attempt && $attempt->started_at) {
            return $attempt->started_at;
        }

        return null;
    }
    
    /**
     * Check if an attempt has expired
     */
    public function isAttemptExpired(Attempt $attempt): bool
    {
        return $attempt->is_expired;
    }
}