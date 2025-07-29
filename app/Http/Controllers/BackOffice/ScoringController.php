<?php

namespace App\Http\Controllers\BackOffice;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Takers\Group;
use Illuminate\Http\Request;
use App\Models\Exams\Question;
use Dentro\Yalr\Attributes\Get;
use App\Models\Attempts\Attempt;
use Dentro\Yalr\Attributes\Post;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Knowledge\Exam\Item\ItemType;
use App\Models\Attempts\AttemptQuestion;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class ScoringController extends Controller
{
    #[Get('back-office/scoring', name: 'back-office.scoring.index')]
    public function index(Request $request): Response
    {
        $deliveries = Delivery::query();
        $deliveries->orderBy('scheduled_at', 'DESC');

        $deliveries->when($request->input('name') ?? false, function ($query, $queryString) {
            $query->where('name', 'like', "%{$queryString}%");
        });

        if ($request->date) {
            $dataRange = explode(' - ', $request->date);
            if (2 === count($dataRange)) {
                $date = new Carbon($dataRange[0]);
                $dateSec = new Carbon($dataRange[1]);
                $deliveries->whereBetween('scheduled_at', [$date, $dateSec]);
            }
        }

        return Inertia::render('BackOffice/Scoring/Index', [
            'payload' => $deliveries->paginate($request->input('perPage', 15))->withQueryString(),
            'tests' => Exam::all(),
            'groups' => Group::all(),
        ]);
    }

    #[Get('back-office/scoring/{delivery_hash}', name: 'back-office.scoring.detail')]
    public function detail(Request $request, Delivery $delivery): Response
    {
        $delivery->load('exam.items.questions', 'group.takers', 'attempts');

        $attempts = Attempt::query()
            ->where('delivery_id', $delivery->id)
            ->where('exam_id', $delivery->exam_id);
        $attempts->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->whereHas('taker', function ($q) use ($queryString) {
                //                $q->where('name', 'like', "%{$queryString}%");
                $q->where('email', 'like', "%{$queryString}%");
            });
            $query->orWherehas('delivery.group.takers', function ($q) use ($queryString) {
                $q->where('taker_code', 'like', "%{$queryString}%");
            });
        });

        $questions = [];
        foreach ($delivery->exam->items as $item) {
            foreach ($item->questions as $question) {
                $questions[] = $question->id;
            }
        }

        return Inertia::render('BackOffice/Scoring/Detail', [
            'takerCount' => $delivery->group->takers->count(),
            'scoringCount' => $delivery->attempts->count(),
            'questionCount' => count($questions),
            'delivery' => $delivery,
            'payload' => $attempts->with(['taker'])->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }

    #[Get('back-office/scoring/{delivery_hash}/pdf/{attempt_hash}', name: 'back-office.scoring.pdf')]
    public function scoringPdf(Request $request, Delivery $delivery, Attempt $attempt): Response
    {
        $attemptQuests = AttemptQuestion::query()->with('question')->where('attempt_id', $attempt->id)->get();
        $taker = $attempt->taker;

        $exam = Exam::query()->with([
            'items' => function ($q) {
                $q->orderBy('order', 'DESC');
            },
            'items.questions.answers',
            'items.attachments',
        ])->where('id', $delivery->exam_id)->first();

        return Inertia::render('BackOffice/Scoring/PDF', [
            'taker' => $taker,
            'takerCode' => $attempt->taker_code,
            'exam' => $exam,
            'itemTypes' => ItemType::getOptions(),
            'attemptQuests' => $attemptQuests,
            'delivery' => $delivery,
        ]);
    }

    #[Get('back-office/scoring/{delivery_hash}/exam/{attempt_hash}', name: 'back-office.scoring.exam')]
    public function scoringShow(Request $request, Delivery $delivery, Attempt $attempt): Response
    {
        $attempt->load('delivery.group');
        $attemptQuests = AttemptQuestion::query()->with('question')->where('attempt_id', $attempt->id)->get();
        $items = Item::query()->whereHas('exams', function ($q) use ($delivery) {
            $q->where('id', $delivery->exam_id);
        })->with(['questions.answers', 'attachments'])->get();

        return Inertia::render('BackOffice/Scoring/Exam', [
            'items' => $items,
            'attempt' => $attempt,
            'attemptQuests' => $attemptQuests,
            'takerCode' => $attempt->taker_code,
            'isInterview' => $delivery->is_interview,
        ]);
    }

    #[Post('back-office/scoring/submit-score', name: 'back-office.scoring.scoring-submit')]
    public function submitScore(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'attempt_hash' => ['required', new ExistsByHash(Attempt::class)],
            'corrects' => 'required|array',
            'scores' => 'required|array',
        ]);

        $attempt = Attempt::byHash($request->attempt_hash);

        foreach ($request->corrects as $key => $correct) {
            $question = Question::byHash($key);
            $attemptQuest = AttemptQuestion::query()->where([
                ['attempt_id', $attempt->id],
                ['question_id', $question->id],
            ])->first();

            $attemptQuest->score = $request->scores[$key];
            $attemptQuest->is_correct = $correct;
            $attemptQuest->save();
        }

        return response()->json([
            'attempt' => $attempt,
        ]);
    }
}
