<?php

namespace App\Http\Controllers\Interview;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\LiveInterview;
use Dentro\Yalr\Attributes\Get;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;

class LiveController extends Controller
{
    #[Get('/back-office/live-interview/{delivery_hash}', name: 'back-office.interview.live-question')]
    public function live(Request $request, Delivery $delivery): \Inertia\Response
    {
        $liveInterviewService = (new LiveInterview($delivery));

        if ($request->has('reset')) {
            $liveInterviewService->clear();
        }

        $lastLive = $liveInterviewService->getLastLive();

        return Inertia::render('Interview/Live', [
            'delivery' => $delivery,
            'lastLive' => $lastLive ?? [
                'question' => null,
                'attempt' => null,
            ],
        ]);
    }
}
