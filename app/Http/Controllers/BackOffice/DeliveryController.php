<?php

namespace App\Http\Controllers\BackOffice;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use Illuminate\Support\Str;
use App\Jobs\CalculateScore;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use App\Models\Exams\Question;
use App\Models\Log\ActivityLog;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Put;
use App\Models\Attempts\Attempt;
use Dentro\Yalr\Attributes\Post;
use Illuminate\Http\JsonResponse;
use Dentro\Yalr\Attributes\Delete;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Knowledge\Exam\Item\ItemType;
use Illuminate\Support\Facades\Session;
use App\Models\Attempts\AttemptQuestion;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class DeliveryController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('allowed:administrator|scorer');
    }

    private function baseValidation()
    {
        return [
            'name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'exam_hash' => ['required', new ExistsByHash(Exam::class)],
            'group_hash' => ['required', new ExistsByHash(Group::class)],
            'scheduled_at' => 'required|date',
            'duration' => 'numeric',
            'automatic_start' => 'boolean',
        ];
    }

    #[Get('back-office/delivery', name: 'back-office.delivery.index')]
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

        return Inertia::render('BackOffice/Delivery/Index', [
            'payload' => $deliveries->paginate($request->input('perPage', 15))->withQueryString(),
            'tests' => Exam::query()->latest()->get(),
            'groups' => Group::query()->whereDate('created_at', '>', Carbon::now()->subYear())->latest()->get(),
        ]);
    }

    #[Post('back-office/delivery', name: 'back-office.delivery.store')]
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate($this->baseValidation());

        $scheduled_at = new Carbon($request->scheduled_at);
        $ended_at = new Carbon($request->scheduled_at);

        $is_interview = Exam::byHash($request->exam_hash)->is_interview;

        $delivery = new Delivery();
        $delivery->name = $request->name;
        $delivery->display_name = $request->display_name;
        $delivery->exam_id = Exam::hashToId($request->exam_hash);
        $delivery->group_id = Group::hashToId($request->group_hash);
        $delivery->scheduled_at = $scheduled_at;

        // IF it is an interview, end time is 23:59 of scheduled day.
        $delivery->ended_at = (! $request->automatic_start || $is_interview)
            ? $ended_at->setTime(23, 59, 59)
            : $ended_at->addMinutes($request->duration);
        $delivery->duration = $is_interview ? 300 : $request->duration;
        $delivery->automatic_start = $is_interview ? false : $request->automatic_start;
        $delivery->is_anytime = $is_interview ? false : ! $request->automatic_start;
        $delivery->save();

        $user = auth()->user();
        $log = ActivityLog::create([
            'log_name' => 'New Delivery Created!',
            'description' => 'Delivery '.$delivery->name.' Created',
        ]);

        $user->logs()->save($log);
        $delivery->logs()->save($log);

        return $this->actionSuccess(message: 'Delivery added successfully.');
    }

    #[Get('back-office/delivery/{delivery_hash}/scoring', name: 'back-office.delivery.scoring')]
    public function showScoring(Request $request, Delivery $delivery): Response
    {
        $attempts = Attempt::query()
            ->where('delivery_id', $delivery->id)
            ->where('exam_id', $delivery->exam_id);

        $attempts->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->whereHas('taker', function ($q) use ($queryString) {
                $q->where('name', 'like', "%{$queryString}%");
                $q->orWhere('email', 'like', "%{$queryString}%");
            });
        });

        return Inertia::render('BackOffice/Delivery/Scoring', array_merge(
            $this->getBaseDataDetail($delivery),
            [
                'payload' => $attempts->with(['taker'])->paginate($request->input('perPage', 15))->withQueryString(),
            ]
        ));
    }

    #[Get('back-office/delivery/{delivery_hash}/taker', name: 'back-office.delivery.taker')]
    public function showTaker(Request $request, Delivery $delivery): Response
    {
        $delivery->load('group');
        $payload = Taker::query();

        $payload->whereHas('groups', function ($query) use ($delivery) {
            $query->where('group_id', $delivery->group->id);
        })->with(['deliveries' => function ($query) use ($delivery) {
            $query->where('delivery_id', $delivery->id);
        }]);

        $payload->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                $q->where('name', 'like', "%{$queryString}%");
                $q->orWhere('email', 'like', "%{$queryString}%");
            });
        });

        $paginatedPayload = $payload
            ->paginate($request->input('perPage', 15))
            ->withQueryString();

        $paginatedPayload->getCollection()->transform(function (Taker $item, $key) use ($delivery) {
            $item->loadTakerCodeOf($delivery);
            $item->loadAttemptHashOf($delivery);

            return $item;
        });

        return Inertia::render('BackOffice/Delivery/Taker', array_merge(
            $this->getBaseDataDetail($delivery),
            [
                'payload' => $paginatedPayload,
            ]
        ));
    }

    #[Get('back-office/delivery/{delivery_hash}/question', name: 'back-office.delivery.question')]
    public function showQuestion(Request $request, Delivery $delivery): Response
    {
        $baseDetail = $this->getBaseDataDetail($delivery, true);
        $question = Question::query()->whereIn('id', $baseDetail['questionId']);

        $question->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where('question', 'like', "%{$queryString}%");
        });

        return Inertia::render('BackOffice/Delivery/Question', array_merge(
            $baseDetail,
            [
                'typeOptions' => ItemType::getOptions(),
                'payload' => $question->with(['item', 'categories'])->paginate($request->input('perPage', 15))->withQueryString(),
            ]
        ));
    }

    #[Put('/back-office/delivery/{delivery_hash}', name: 'back-office.delivery.update')]
    public function update(Request $request, Delivery $delivery): \Illuminate\Http\RedirectResponse
    {
        $request->validate($this->baseValidation());

        $scheduled_at = new Carbon($request->scheduled_at);
        $ended_at = new Carbon($request->scheduled_at);

        $delivery->name = $request->name;
        $delivery->display_name = $request->display_name;
        $delivery->exam_id = Exam::hashToId($request->exam_hash);
        $delivery->group_id = Group::hashToId($request->group_hash);
        $delivery->scheduled_at = $scheduled_at;
        $delivery->ended_at = (! $request->automatic_start) ? $ended_at->setTime(23, 59, 59) : $ended_at->addMinutes($request->duration);
        $delivery->duration = $request->duration;
        $delivery->automatic_start = $request->automatic_start;
        $delivery->is_anytime = ! $request->automatic_start;
        $delivery->save();

        return $this->actionSuccess(message: 'Delivery updated successfully.');
    }

    #[Delete('/back-office/delivery/{delivery_hash}', name: 'back-office.delivery.destroy')]
    public function destroy(Delivery $delivery): \Illuminate\Http\RedirectResponse
    {
        $delivery->delete();

        return $this->actionSuccess(route('back-office.delivery.index'), 'Delivery deleted successfully.');
    }

    #[Post('/back-office/delivery/{delivery_hash}/generate-token', name: 'back-office.delivery.generate-token')]
    public function generateToken(Request $request, Delivery $delivery): \Illuminate\Http\RedirectResponse
    {
        if (! $request->hash) {
            $delivery->load('group.takers');

            $takers = [];
            foreach ($delivery->group->takers as $taker) {
                $takers[$taker->id] = [
                    'token' => $this->getRandomToken(),
                    'is_login' => false,
                ];
            }

            $delivery->takers()->sync($takers);

            if ($delivery->is_interview) {
                $delivery
                    ->takers()
                    ->cursor()
                    ->each(fn ($taker) => $this->attemptInterview($delivery, $taker));
            }

            return $this->actionSuccess(message: 'Successfully generate all token.');
        }

        $takerId = Taker::hashToId($request->hash);
        $taker[$takerId] = [
            'token' => $this->getRandomToken(),
            'is_login' => false,
        ];
        $delivery->takers()->sync($taker, false);

        $this->attemptInterview($delivery, Taker::find($takerId));

        return $this->actionSuccess(message: 'Successfully generate Candidate token.');
    }

    #[Get('back-office/delivery/{delivery_hash}/scoring/{attempt_hash}/pdf', name: 'back-office.delivery.scoring-pdf')]
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

        return Inertia::render('BackOffice/Delivery/ScoringPDF', [
            'taker' => $taker,
            'takerCode' => $attempt->taker_code,
            'exam' => $exam,
            'itemTypes' => ItemType::getOptions(),
            'attemptQuests' => $attemptQuests,
            'delivery' => $delivery,
        ]);
    }

    #[Get('back-office/delivery/{delivery_hash}/scoring/{attempt_hash}/detail', name: 'back-office.delivery.scoring-detail')]
    public function scoringShow(Request $request, Delivery $delivery, Attempt $attempt): Response
    {
        $attempt->load(['delivery.group', 'taker']);
        $attemptQuests = AttemptQuestion::query()->with('question')->where('attempt_id', $attempt->id)->get();
        $items = Item::query()->whereHas('exams', function ($q) use ($delivery) {
            $q->where('id', $delivery->exam_id);
        })->with(['questions.answers', 'attachments'])->get();

        return Inertia::render('BackOffice/Delivery/ScoringDetail', [
            'items' => $items,
            'attempt' => $attempt,
            'attemptQuests' => $attemptQuests,
            'takerCode' => $attempt->taker_code,
            'isInterview' => $delivery->is_interview,
        ]);
    }

    #[Post('back-office/delivery/scoring', name: 'back-office.delivery.scoring-submit')]
    public function submitScore(Request $request): JsonResponse
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

            if ($attemptQuest) {
                $attemptQuest->score = $request->scores[$key] ?? 0;
                $attemptQuest->is_correct = $correct;
                $attemptQuest->save();
            }
        }

        return response()->json([
            'attempt' => $attempt,
        ]);
    }

    #[Post('back-office/delivery/scoring-question', name: 'back-office.delivery.question-scoring-submit')]
    public function submitQuestionScoring(Request $request): JsonResponse
    {
        $request->validate([
            'attempt_hash' => ['required', new ExistsByHash(Attempt::class)],
            'question_hash' => ['required', new ExistsByHash(Question::class)],
            'score' => 'required',
            'note' => '', // is interview
            'correct' => 'required',
        ]);

        $attempt = Attempt::byHash($request->attempt_hash);
        $question = Question::byHash($request->question_hash);
        $is_interview = $attempt->delivery->is_interview;

        if ($is_interview) {
            $attemptQuest = AttemptQuestion::query()->updateOrCreate([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ], [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_id' => null,
                'answer_hash' => null,
                'answer' => $request->note,
                'score' => $request->score,
                'is_correct' => $request->correct,
            ]);

            $this->dispatch(new CalculateScore($attempt));

            return response()->json([
                'attempt' => $attempt,
                'attempt_question' => $attemptQuest,
            ]);
        }

        $attemptQuest = AttemptQuestion::query()->where([
            ['attempt_id', $attempt->id],
            ['question_id', $question->id],
        ])->first();

        if ($attemptQuest) {
            $attemptQuest->score = $request->score ?? 0;
            $attemptQuest->is_correct = $request->correct;
            $attemptQuest->save();
        }

        $this->dispatch(new CalculateScore($attempt));

        return response()->json([
            'attempt' => $attempt,
            'attempt_question' => $attemptQuest,
        ]);
    }

    #[Post('back-office/delivery/finish-scoring', name: 'back-office.delivery.finish-scoring')]
    public function finishAndCalculateScore(Request $request): JsonResponse
    {
        $request->validate([
            'attempt_hash' => ['required', new ExistsByHash(Attempt::class)],
        ]);

        $attempt = Attempt::byHash($request->attempt_hash);

        $this->dispatch(new CalculateScore($attempt));

        $attempt->finish_scoring = true;
        $attempt->save();

        return response()->json($attempt);
    }

    #[Post('back-office/delivery/{delivery_hash}/finish', name: 'back-office.delivery.finish')]
    public function setToFinish(Request $request, Delivery $delivery): \Illuminate\Http\RedirectResponse
    {
        $delivery->is_finished = Carbon::now();
        $delivery->save();

        return $this->actionSuccess(message: 'Successfully finished delivery.');
    }

    #[Get('back-office/delivery/{delivery_hash}/goto', name: 'back-office.delivery.goto')]
    public function goto(Request $request, Delivery $delivery): \Illuminate\Http\RedirectResponse
    {
        Session::forget('exam');
        Session::put('exam', [
            'token' => null,
            'taker' => null,
            'delivery' => $delivery,
            'admin' => auth()->user()->hash,
        ]);

        return redirect('/exam');
    }

    #[Post('back-office/delivery/{delivery_hash}/takers/{taker_hash}/interview', name: 'back-office.delivery.attempt-interview')]
    public function attemptInterview(Delivery $delivery, Taker $taker, bool $response = true): bool|JsonResponse
    {
        /** @var Attempt $attempt */
        $attempt = $taker->attempts()->updateOrCreate([
            'delivery_id' => $delivery->id,
            'exam_id' => $delivery->exam->id,
        ], [
            'delivery_id' => $delivery->id,
            'exam_id' => $delivery->exam->id,
            'ip_address' => \Illuminate\Support\Facades\Request::ip(),
            'started_at' => Carbon::now(),
        ]);

        if ($response) {
            $attempt = $attempt->fresh();

            return response()->json([
                'attempt_hash' => $attempt->hash,
            ]);
        }

        return true;
    }

    // # CUSTOM METHODS ##

    private function getBaseDataDetail(Delivery $delivery, $getIdQuestions = false): array
    {
        $delivery->load('exam.items.questions', 'group.takers', 'attempts');
        $questions = [];
        foreach ($delivery->exam->items as $item) {
            foreach ($item->questions as $question) {
                $questions[] = $question->id;
            }
        }

        $baseDetail = [
            'takerCount' => $delivery->group->takers->count(),
            'scoringCount' => $delivery->attempts->count(),
            'questionCount' => count($questions),
            'delivery' => $delivery,
            'tests' => Exam::all(),
            'groups' => Group::all(),
        ];

        if ($getIdQuestions) {
            $baseDetail['questionId'] = $questions;
        }

        return $baseDetail;
    }

    public function getRandomToken(): string
    {
        return Str::random(5);
    }
}
