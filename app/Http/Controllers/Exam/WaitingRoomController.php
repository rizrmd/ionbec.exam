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

        // Debug: Log the actual session structure
        \Log::info('WaitingRoomController: Session data structure', [
            'session_data' => $dataSession,
            'session_keys' => $dataSession ? array_keys($dataSession) : [],
            'url' => request()->url()
        ]);

        // Check if exam session exists
        if (! $dataSession || ! isset($dataSession['delivery']) || ! isset($dataSession['taker'])) {
            \Log::warning('WaitingRoomController: No valid exam session found', [
                'session_exists' => !empty($dataSession),
                'has_delivery' => isset($dataSession['delivery']),
                'has_taker' => isset($dataSession['taker']),
                'url' => request()->url()
            ]);

            // Redirect to home page with error message
            return redirect('/')->with('error', 'No direct access allowed. Please enter your exam token first.');
        }

        // Debug: Check delivery data structure
        \Log::info('WaitingRoomController: Delivery data', [
            'delivery_data' => $dataSession['delivery'],
            'delivery_id' => isset($dataSession['delivery']->id) ? $dataSession['delivery']->id : 'not set',
            'delivery_type' => gettype($dataSession['delivery'])
        ]);

        $delivery = Delivery::query()->where('id', $dataSession['delivery']->id)->first();

        // Check if delivery exists
        if (! $delivery) {
            \Log::error('WaitingRoomController: Delivery not found', [
                'delivery_id' => $dataSession['delivery']->id ?? 'unknown'
            ]);
            return redirect('/')->with('error', 'Exam delivery not found. Please contact support.');
        }

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
