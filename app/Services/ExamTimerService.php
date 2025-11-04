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
            'remaining_seconds' => $remainingSeconds
        ]);
        
        return $remainingSeconds;
    }
    
    /**
     * Determine the exam start time based on delivery type and attempt
     */
    private function determineStartTime(Delivery $delivery, Attempt $attempt = null): ?Carbon
    {
        if ($delivery->automatic_start) {
            return $delivery->scheduled_at;
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