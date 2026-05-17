<?php

namespace App\Http\Controllers\Exam;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Takers\Taker;
use Dentro\Yalr\Attributes\Get;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Services\ExamAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class WaitingRoomController extends Controller
{
    #[Get('/exam/waiting-room', name: 'exam.waiting-room')]
    public function index(): Response|\Illuminate\Http\RedirectResponse
    {
        $dataSession = Session::get('exam');
        $resolved = app(ExamAccessService::class)->resolveSession();

        // Debug: Log the actual session structure
        \Log::info('WaitingRoomController: Session data structure', [
            'session_data' => $dataSession,
            'session_keys' => $dataSession ? array_keys($dataSession) : [],
            'url' => request()->url()
        ]);

        // Check if exam session exists
        if (! $dataSession || ! $resolved) {
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

        $delivery = $resolved['delivery'];

        // Check if delivery exists
        if (! $delivery) {
            \Log::error('WaitingRoomController: Delivery not found', [
                'delivery_id' => $dataSession['delivery']->id ?? 'unknown'
            ]);
            return redirect('/')->with('error', 'Exam delivery not found. Please contact support.');
        }

        $status = app(ExamAccessService::class)->waitingRoomStatus($delivery);
        if ($status['can_start']) {
            return redirect()->route('exam.main');
        }

        return Inertia::render('Exam/WaitingRoom', [
            'delivery' => $delivery,
            'taker' => $resolved['taker'],
            'status' => $status,
        ]);
    }

    #[Get('/exam/waiting-room/status', name: 'exam.waiting-room.status')]
    public function status(): JsonResponse
    {
        $resolved = app(ExamAccessService::class)->resolveSession();

        if (! $resolved) {
            return response()->json([
                'can_start' => false,
                'invalid_session' => true,
                'message' => 'No valid exam session found.',
            ], 401);
        }

        return response()->json(app(ExamAccessService::class)->waitingRoomStatus($resolved['delivery']));
    }
}
