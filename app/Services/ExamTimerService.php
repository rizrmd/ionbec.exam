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
            // 🔧 DEMO MODE FIX: If no valid start time, check if this is a DEMO exam
            if ($this->isDemoExam($delivery)) {
                \Log::info('ExamTimerService: Using fallback for DEMO exam - returning full duration', [
                    'delivery_id' => $delivery->id,
                    'base_duration' => $baseDuration,
                    'demo_fallback' => true
                ]);
                return $baseDuration * 60; // Return full duration in seconds
            }
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
     * Check if this is a DEMO/TEST exam that needs fallback handling
     */
    private function isDemoExam(Delivery $delivery): bool
    {
        $deliveryName = strtolower($delivery->name ?? '');

        // 🔧 ENHANCEMENT: Configurable DEMO indicators with sensible defaults
        $demoIndicators = config('exam.demo_keywords', ['demo', 'test', 'trial', 'try out']);

        foreach ($demoIndicators as $indicator) {
            if (strpos($deliveryName, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Determine the exam start time based on delivery type and attempt
     */
    private function determineStartTime(Delivery $delivery, Attempt $attempt = null): ?Carbon
    {
        // 🔧 CRITICAL FIX: Handle automatic_start with fallback to attempt start time
        if ($delivery->automatic_start) {
            // If scheduled_at is reasonable (not too old and not too far in future), use it
            $scheduledTime = Carbon::parse($delivery->scheduled_at);
            $now = Carbon::now();
            $hoursDiff = abs($scheduledTime->diffInHours($now));

            // 🔧 ENHANCEMENT: Configurable validation threshold (default 24 hours)
            $maxHoursDiff = config('exam.timer_max_hours_diff', 24);

            // Only use scheduled_at if it's within reasonable range (configurable max hours)
            if ($hoursDiff <= $maxHoursDiff && $scheduledTime->lte($now)) {
                return $scheduledTime;
            }

            // 🔒 DEMO MODE FIX: For automatic_start with invalid scheduled time,
            // use attempt start time instead (more reliable for DEMO/Testing)
            if ($attempt && $attempt->started_at) {
                \Log::info('ExamTimerService: Using attempt start time instead of invalid scheduled time', [
                    'delivery_id' => $delivery->id,
                    'scheduled_at' => $scheduledTime->toDateTimeString(),
                    'attempt_started_at' => $attempt->started_at->toDateTimeString(),
                    'hours_diff' => $hoursDiff
                ]);
                return $attempt->started_at;
            }

            // If no attempt or invalid scheduled time, return null to handle gracefully
            \Log::warning('ExamTimerService: Invalid scheduled time and no attempt available', [
                'delivery_id' => $delivery->id,
                'scheduled_at' => $scheduledTime->toDateTimeString(),
                'automatic_start' => $delivery->automatic_start
            ]);
            return null;
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