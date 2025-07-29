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
    public function index(): Response
    {
        $dataSession = Session::get('exam');

        return Inertia::render('Exam/WaitingRoom', [
            'delivery' => Delivery::query()->where('id', $dataSession['delivery']->id)->first(),
            'taker' => Taker::query()->where('id', $dataSession['taker']->id)->first(),
        ]);
    }
}
