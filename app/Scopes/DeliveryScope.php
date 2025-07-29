<?php

namespace App\Scopes;

use Carbon\Carbon;
use App\Models\Deliveries\Delivery;

trait DeliveryScope
{
    public static function getStatus(Delivery $delivery): string
    {
        if ($delivery->is_finished) {
            return Delivery::STATUS_FINISHED;
        }

        $now = Carbon::now();
        $schedule = Carbon::parse($delivery->scheduled_at);
        $ended = null;

        if ($delivery->is_anytime && null !== $delivery->ended_at) {
            $ended = Carbon::parse($delivery->ended_at);
        } elseif (! $delivery->is_anytime) {
            $ended = Carbon::parse($delivery->scheduled_at)->addMinutes($delivery->duration);
        }

        if ($delivery->is_interview && ($now->gt($schedule) || $schedule->isCurrentDay())) {
            return Delivery::STATUS_SCORING;
        }

        if ($ended && $now->between($schedule, $ended)) {
            // takers not ready, but is on progress.
            if (! $delivery->takers_ready) {
                return Delivery::STATUS_NOT_STARTED;
            }

            return Delivery::STATUS_ON_PROGRESS;
        }

        if ($now->gt($schedule)) {
            // takers not ready, but overdue from schedule.
            if (! $delivery->takers_ready) {
                return Delivery::STATUS_OVERDUE;
            }

            return Delivery::STATUS_SCORING;
        }

        // takers not ready, but still upcoming.
        if (! $delivery->takers_ready) {
            return Delivery::STATUS_CREATED;
        }

        return Delivery::STATUS_SCHEDULED;
    }
}
