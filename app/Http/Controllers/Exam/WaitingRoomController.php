<?php

namespace App\Http\Controllers\Exam;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Takers\Taker;
use Dentro\Yalr\Attributes\Get;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class WaitingRoomController extends Controller
{
    #[Get('/exam/waiting-room', name: 'exam.waiting-room')]
    public function index(): Response|\Illuminate\Http\RedirectResponse
    {
        $dataSession = Session::get('exam');
        $delivery = Delivery::query()->where('id', $dataSession['delivery']->id)->first();

        // Force redirect to exam if scheduled time has passed
        if ($delivery && $delivery->automatic_start) {
            $scheduledTime = strtotime($delivery->scheduled_at);
            $currentTime = strtotime('now');
            $timeDiff = $scheduledTime - $currentTime;

            \Log::info('WaitingRoomController: Time check', [
                'delivery_id' => $delivery->id,
                'delivery_name' => $delivery->name,
                'scheduled_at' => $delivery->scheduled_at,
                'scheduled_time' => $scheduledTime,
                'current_time' => $currentTime,
                'time_diff_seconds' => $timeDiff,
                'should_redirect_to_exam' => $timeDiff <= 0
            ]);

            // If exam time has passed or is now, redirect immediately to exam
            if ($timeDiff <= 0) {
                \Log::info('WaitingRoomController: Redirecting to exam - time has passed');
                return redirect()->route('exam.main');
            }
        }

        return Inertia::render('Exam/WaitingRoom', [
            'delivery' => $delivery,
            'taker' => Taker::query()->where('id', $dataSession['taker']->id)->first(),
        ]);
    }
}
