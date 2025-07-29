<?php

namespace App\Http\Controllers\Taker;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Exams\Exam;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use App\Models\Attempts\Attempt;
use Dentro\Yalr\Attributes\Post;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Knowledge\Exam\Item\ItemType;
use Illuminate\Support\Facades\Session;
use App\Models\Attempts\AttemptQuestion;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class ScheduleController extends Controller
{
    #[Get('/schedules', name: 'taker.schedules.index')]
    public function index(Request $request): Response
    {
        $taker = auth()->guard('taker')->user();

        $deliveries = Delivery::query()
            ->with('takers')
            ->whereHas('takers', function ($q) use ($taker) {
                $q->where('id', $taker->id);
            })->orderBy('scheduled_at', 'DESC');

        $paginate = $deliveries->paginate($request->input('perPage', 15))->withQueryString();
        $paginate->setCollection($paginate->getCollection()->map(function ($item) use ($taker) {
            $item->token = $item->takers()->where('id', $taker->id)->first()->pivot->token;

            return $item;
        }));

        return Inertia::render('Taker/Schedule/Index', [
            'payload' => $paginate,
        ]);
    }

    #[Post('/schedules', name: 'taker.schedules.login')]
    public function login(Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $request->validate(['delivery_hash' => ['required', new ExistsByHash(Delivery::class)]]);
        $delivery = Delivery::byHash($request->delivery_hash);
        $taker = auth()->guard('taker')->user();

        Session::forget('exam');
        Session::put('exam', [
            'token' => null,
            'taker' => $taker,
            'delivery' => $delivery,
            'admin' => null,
        ]);

        // Check also MainController->index
        if ($delivery->automatic_start) {
            if (strtotime($delivery->scheduled_at) >= strtotime('now')) {
                return redirect()->route('exam.waiting-room');
            }
        }

        return redirect('/exam');
    }

    #[Get('/schedules/{delivery_hash}/pdf', name: 'taker.schedules.pdf')]
    public function pdf(Request $request, Delivery $delivery): Response
    {
        $attempt = Attempt::query()->where([
            ['delivery_id', $delivery->id],
            ['attempted_by', auth()->guard('taker')->user()->id],
        ])->first();

        $attemptQuests = AttemptQuestion::query()->with('question')->where('attempt_id', $attempt->id)->get();

        $exam = Exam::query()->with([
            'items' => function ($q) {
                $q->orderBy('order', 'DESC');
            },
            'items.questions.answers',
            'items.attachments',
        ])->where('id', $delivery->exam_id)->first();

        return Inertia::render('Taker/Schedule/PDF', [
            'exam' => $exam,
            'itemTypes' => ItemType::getOptions(),
            'attemptQuests' => $attemptQuests,
            'delivery' => $delivery,
        ]);
    }
}
