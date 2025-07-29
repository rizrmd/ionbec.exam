<?php

namespace App\Http\Controllers\Exam;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Takers\Taker;
use Dentro\Yalr\Attributes\Get;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class FinishedController extends Controller
{
    #[Get('/exam/finished', name: 'exam.finished')]
    public function index()
    {
        $dataSession = Session::get('exam');

        if (is_null($dataSession)) {
            return redirect('/');
        }

        if (null !== $dataSession['admin']) {
            Session::forget('exam');

            return redirect('/');
        }

        $deliveryId = $dataSession['delivery']->id;
        $takerId = $dataSession['taker']->id;

        $delivery = Delivery::query()->where('id', $deliveryId)->first();
        $taker = Taker::query()->where('id', $takerId)->first();

        $taker->attempts()->where('delivery_id', $deliveryId)->update([
            'ended_at' => Carbon::now(),
        ]);

        Session::forget('exam');

        return Inertia::render('Exam/Finished', [
            'delivery' => $delivery,
            'taker' => $taker,
        ]);
    }
}
