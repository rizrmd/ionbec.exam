<?php

namespace App\Http\Controllers\Taker;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Dentro\Yalr\Attributes\Get;
use App\Models\Attempts\Attempt;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Attempts\AttemptQuestion;

class DashboardController extends Controller
{
    private function deliveryQuery($taker): \Illuminate\Database\Eloquent\Builder
    {
        return Delivery::query()->whereHas('takers', function ($q) use ($taker) {
            $q->where('id', $taker->id);
        });
    }

    #[Get('/dashboard', name: 'taker.dashboard')]
    public function index(): Response
    {
        $taker = auth()->guard('taker')->user();
        $delivery = $this
            ->deliveryQuery($taker)->with(['exam', 'group.takers'])
            ->where('scheduled_at', '>', Carbon::now())->limit(5)->get();

        return Inertia::render('Taker/Dashboard', [
            'upcomingDelivery' => $delivery,
        ]);
    }

    #[Get('/dashboard/data', name: 'taker.dashboard.card-data')]
    public function cardData(): \Illuminate\Http\JsonResponse
    {
        $taker = auth()->guard('taker')->user();
        $upcomingDelivery = $this->deliveryQuery($taker)->whereDate('scheduled_at', '>', Carbon::now())->count();
        $finishedDelivery = $this->deliveryQuery($taker)->whereDate('scheduled_at', '<', Carbon::now())->count();

        $attemptIds = Attempt::query()->where('attempted_by', $taker->id)->pluck('id');
        $attemptQuest = AttemptQuestion::query()
            ->whereIn('attempt_id', $attemptIds)
            ->where('is_correct', true)
            ->avg('score');

        $lastAttempt = Attempt::query()->where('attempted_by', $taker->id)->orderBy('created_at', 'DESC')->first();

        return response()->json([
            'upcomingDelivery' => $upcomingDelivery,
            'finishedDelivery' => $finishedDelivery,
            'averageScore' => (null === $attemptQuest) ? 0 : round($attemptQuest, 2),
            'lastAttempt' => $lastAttempt,
        ]);
    }
}
