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
use App\Services\ExamSnapshotService;

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

        $paginatedDeliveries = $deliveries->paginate($request->input('perPage', 15))->withQueryString();
        
        // Manually load exam relationships to bypass ClientScope issues
        $paginatedDeliveries->getCollection()->transform(function ($delivery) {
            $exam = \App\Models\Exams\Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('id', $delivery->exam_id)
                ->first();
            $delivery->setRelation('exam', $exam);
            return $delivery;
        });

        return Inertia::render('BackOffice/Delivery/Index', [
            'payload' => $paginatedDeliveries,
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
        $delivery->duration = $request->duration;
        $delivery->automatic_start = $is_interview ? false : $request->automatic_start;
        $delivery->is_anytime = $is_interview ? false : ! $request->automatic_start;
        $delivery->save();

        // Create exam snapshot for this delivery to ensure exam integrity
        try {
            $snapshotService = app(ExamSnapshotService::class);
            $snapshotService->createSnapshot($delivery);
        } catch (\Exception $e) {
            \Log::error('Failed to create delivery snapshot', [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail delivery creation if snapshot fails - it will use live exam as fallback
        }

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
    public function showScoring(Request $request, string $delivery_hash): Response
    {
        // Extract actual hash if we received JSON serialized object
        // This handles cases where route model binding returns serialized delivery instead of hash
        if (is_string($delivery_hash) && str_starts_with($delivery_hash, '{')) {
            $decoded = json_decode($delivery_hash, true);
            $delivery_hash = $decoded['hash'] ?? $delivery_hash;
        }
        
        // Find delivery by hash using HashableId trait
        try {
            $delivery = Delivery::byHash($delivery_hash);
        } catch (\Exception $e) {
            $delivery = null;
        }
        
        if (!$delivery) {
            abort(404, 'Delivery not found');
        }
        
        $attempts = Attempt::query()
            ->where('delivery_id', $delivery->id)
            ->where('exam_id', $delivery->exam_id);

        // Apply search filter using direct join to avoid ClientScope issues
        $attempts->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->join('takers', 'attempts.attempted_by', '=', 'takers.id')
                ->where(function ($q) use ($queryString) {
                    $q->where('takers.name', 'like', "%{$queryString}%")
                      ->orWhere('takers.email', 'like', "%{$queryString}%");
                })
                ->select('attempts.*'); // Only select attempts columns to avoid conflicts
        });

        $paginatedAttempts = $attempts->paginate($request->input('perPage', 15))->withQueryString();
        
        // Transform each attempt to manually load the taker relationship
        $paginatedAttempts->getCollection()->transform(function ($attempt) {
            $taker = \App\Models\Takers\Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('id', $attempt->attempted_by)
                ->first();
            $attempt->setRelation('taker', $taker);
            return $attempt;
        });

        return Inertia::render('BackOffice/Delivery/Scoring', array_merge(
            $this->getBaseDataDetail($delivery),
            [
                'payload' => $paginatedAttempts,
            ]
        ));
    }

    #[Get('back-office/delivery/{delivery_hash}/taker', name: 'back-office.delivery.taker')]
    public function showTaker(Request $request, Delivery $delivery): Response
    {
        // Load group without ClientScope to ensure it loads even across different clients
        $group = \App\Models\Takers\Group::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $delivery->group_id)
            ->first();

        // Set the relationship for Inertia serialization
        if ($group) {
            $delivery->setRelation('group', $group);
        }

        if (!$group) {
            // Return empty paginated response to match expected format
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], // empty items
                0,  // total items
                $request->input('perPage', 15), // items per page
                1,  // current page
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return Inertia::render('BackOffice/Delivery/Taker', array_merge(
                $this->getBaseDataDetail($delivery),
                ['payload' => $emptyPaginator]
            ));
        }

        // Query takers without ClientScope to ensure cross-client visibility
        $payload = Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->whereHas('groups', function ($query) use ($group) {
                $query->withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('group_id', $group->id);
            })->with(['deliveries' => function ($query) use ($delivery) {
            $query->withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('delivery_id', $delivery->id);
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
    public function showQuestion(Request $request, string $delivery_hash): Response
    {
        // Extract actual hash if we received JSON serialized object
        if (is_string($delivery_hash) && str_starts_with($delivery_hash, '{')) {
            $decoded = json_decode($delivery_hash, true);
            $delivery_hash = $decoded['hash'] ?? $delivery_hash;
        }
        
        // Find delivery by hash using HashableId trait
        try {
            $delivery = Delivery::byHash($delivery_hash);
        } catch (\Exception $e) {
            $delivery = null;
        }
        
        if (!$delivery) {
            abort(404, 'Delivery not found');
        }
        
        $baseDetail = $this->getBaseDataDetail($delivery, true);
        $question = Question::query()->whereIn('id', $baseDetail['questionId']);

        $question->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                // Search in question text
                $q->where('questions.question', 'like', "%{$queryString}%");

                // Also search in item content if it's a vignette
                $q->orWhereHas('item', function ($itemQuery) use ($queryString) {
                    $itemQuery->where('items.is_vignette', true)
                             ->where('items.content', 'like', "%{$queryString}%");
                });
            });
        });

        $paginatedQuestions = $question->paginate($request->input('perPage', 15))->withQueryString();
        
        // Manually load item and categories relationships to bypass ClientScope issues
        $paginatedQuestions->getCollection()->transform(function ($questionItem) {
            $item = \App\Models\Exams\Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('id', $questionItem->item_id)
                ->first();
            $questionItem->setRelation('item', $item);
            
            $categories = $questionItem->categories()->withoutGlobalScope(\App\Scopes\ClientScope::class)->get();
            $questionItem->setRelation('categories', $categories);
            
            return $questionItem;
        });

        return Inertia::render('BackOffice/Delivery/Question', array_merge(
            $baseDetail,
            [
                'typeOptions' => ItemType::getOptions(),
                'payload' => $paginatedQuestions,
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
            $group = $delivery->group;
            if (!$group) {
                return response()->json(['error' => 'Group not found'], 404);
            }
            
            $group->load('takers');
            $takers = [];
            foreach ($group->takers as $taker) {
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

    #[Get('back-office/delivery/{delivery_hash}/taker-pdf', name: 'back-office.delivery.taker-pdf')]
    public function takerPdf(Request $request, Delivery $delivery)
    {
        // Load group without ClientScope
        $group = \App\Models\Takers\Group::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $delivery->group_id)
            ->first();

        if (!$group) {
            abort(404, 'Group not found for this delivery');
        }

        // Load all takers with their tokens
        $takers = Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->whereHas('groups', function ($query) use ($group) {
                $query->withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('group_id', $group->id);
            })
            ->with(['deliveries' => function ($query) use ($delivery) {
                $query->withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('delivery_id', $delivery->id);
            }])
            ->orderBy('name', 'ASC')
            ->get();

        // Transform takers to include taker_code and token
        $takers->transform(function (Taker $taker) use ($delivery) {
            $taker->loadTakerCodeOf($delivery);

            // Get token from pivot table
            $deliveryPivot = $taker->deliveries->first();
            $taker->token = $deliveryPivot ? $deliveryPivot->pivot->token : null;

            return $taker;
        });

        // Return Inertia view for browser-based PDF generation
        return Inertia::render('BackOffice/Delivery/TakerPDF', [
            'delivery' => $delivery,
            'group' => $group,
            'takers' => $takers,
        ]);
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
        // Manually load delivery and group relationships to bypass ClientScope issues
        $deliveryData = \App\Models\Deliveries\Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $attempt->delivery_id)
            ->first();
        
        if ($deliveryData) {
            $group = \App\Models\Takers\Group::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('id', $deliveryData->group_id)
                ->first();
            if ($group) {
                $deliveryData->setRelation('group', $group);
            }
            $attempt->setRelation('delivery', $deliveryData);
        }
        
        // Manually load taker relationship
        $taker = \App\Models\Takers\Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $attempt->attempted_by)
            ->first();
        if ($taker) {
            $attempt->setRelation('taker', $taker);
        }
        
        $attemptQuests = AttemptQuestion::query()->with('question')->where('attempt_id', $attempt->id)->get();
        
        // Use direct join to bypass ClientScope issues
        $items = Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->join('exam_item', 'items.id', '=', 'exam_item.item_id')
            ->where('exam_item.exam_id', $delivery->exam_id)
            ->with(['questions.answers', 'attachments'])
            ->select('items.*')
            ->orderBy('exam_item.order')
            ->get();

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

        // Check if all questions are now scored and automatically finish scoring
        $this->checkAndFinishScoring($attempt);

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
        
        $delivery = $attempt->delivery;
        if (!$delivery) {
            return response()->json(['error' => 'Delivery not found'], 404);
        }
        
        $is_interview = $delivery->is_interview;

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

        // Check if all questions are now scored and automatically finish scoring
        $this->checkAndFinishScoring($attempt);

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
    public function goto(Request $request, string $delivery_hash): \Illuminate\Http\RedirectResponse
    {
        // Extract actual hash if we received JSON serialized object
        if (is_string($delivery_hash) && str_starts_with($delivery_hash, '{')) {
            $decoded = json_decode($delivery_hash, true);
            $delivery_hash = $decoded['hash'] ?? $delivery_hash;
        }
        
        // Find delivery by hash using HashableId trait
        try {
            $delivery = Delivery::byHash($delivery_hash);
        } catch (\Exception $e) {
            $delivery = null;
        }
        
        if (!$delivery) {
            abort(404, 'Delivery not found');
        }
        
        Session::forget('exam');
        Session::put('exam', [
            'token' => null,
            'taker' => null,
            'delivery' => $delivery,
            'admin' => auth()->user()->hash,
        ]);

        return redirect("/back-office/delivery/{$delivery_hash}/preview");
    }

    #[Get('back-office/delivery/{delivery_hash}/preview', name: 'back-office.delivery.preview')]
    public function preview(Request $request, string $delivery_hash): Response
    {
        // Extract actual hash if we received JSON serialized object
        if (is_string($delivery_hash) && str_starts_with($delivery_hash, '{')) {
            $decoded = json_decode($delivery_hash, true);
            $delivery_hash = $decoded['hash'] ?? $delivery_hash;
        }
        
        // Find delivery by hash using HashableId trait
        try {
            $delivery = Delivery::byHash($delivery_hash);
        } catch (\Exception $e) {
            $delivery = null;
        }
        
        if (!$delivery) {
            abort(404, 'Delivery not found');
        }
        
        // Load exam using the fixed relationship approach
        $exam = \App\Models\Exams\Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $delivery->exam_id)
            ->first();
            
        if (!$exam) {
            return redirect()->route('back-office.delivery.index')->with('error', 'Exam not found');
        }

        // Load items through exam relationship to get proper ordering
        $items = $exam->items()->with(['questions.answers', 'attachments'])->orderByPivot('order')->get();

        // hiding is_correct_answer column for admin preview
        foreach ($items as $item) {
            $questions = $item->questions;
            $questions->each(function ($question, $questionKey) use ($questions) {
                if ($question->answers) {
                    $questions[$questionKey]->answers->each(function ($answer, $answerKey) use ($questions, $questionKey) {
                        unset($questions[$questionKey]->answers[$answerKey]->is_correct_answer);
                    });
                }
            });
        }

        $data = [
            'delivery' => $delivery,
            'taker' => null, // Admin preview, no taker
            'exam' => $exam,
            'examItems' => $items,
            'admin' => auth()->user(),
            'isAdminPreview' => true,
        ];

        return Inertia::render('Exam/Main', $data);
    }

    #[Post('back-office/delivery/{delivery_hash}/takers/{taker_hash}/interview', name: 'back-office.delivery.attempt-interview')]
    public function attemptInterview(Delivery $delivery, Taker $taker, bool $response = true): bool|JsonResponse
    {
        $exam = $delivery->exam;
        if (!$exam) {
            return response()->json(['error' => 'Exam not found'], 404);
        }
        
        /** @var Attempt $attempt */
        $attempt = $taker->attempts()->updateOrCreate([
            'delivery_id' => $delivery->id,
            'exam_id' => $exam->id,
        ], [
            'delivery_id' => $delivery->id,
            'exam_id' => $exam->id,
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
        // Load relationships separately due to ClientScope issues
        $delivery->load('attempts');
        
        // Manually load exam relationship bypassing ClientScope
        $exam = \App\Models\Exams\Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $delivery->exam_id)
            ->first();
        $questions = [];
        
        if ($exam) {
            // Load items and questions without ClientScope using direct DB query
            $items = \App\Models\Exams\Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->join('exam_item', 'items.id', '=', 'exam_item.item_id')
                ->where('exam_item.exam_id', $delivery->exam_id)
                ->with(['questions' => function ($q) {
                    $q->withoutGlobalScope(\App\Scopes\ClientScope::class);
                }])
                ->select('items.*') // Select only items columns to avoid conflicts
                ->get();
            
            $exam->setRelation('items', $items);
            
            foreach ($items as $item) {
                foreach ($item->questions as $question) {
                    $questions[] = $question->id;
                }
            }
        }

        // Manually load group relationship bypassing ClientScope
        $group = \App\Models\Takers\Group::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $delivery->group_id)
            ->first();
        $takerCount = 0;
        if ($group) {
            // Load takers without ClientScope
            $takers = \App\Models\Takers\Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->whereHas('groups', function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                })
                ->get();
            $group->setRelation('takers', $takers);
            $takerCount = $takers->count();
        }

        // Ensure the delivery object has the relationships for frontend serialization
        if ($exam) {
            $delivery->setRelation('exam', $exam);
        }
        if ($group) {
            $delivery->setRelation('group', $group);
        }

        $baseDetail = [
            'takerCount' => $takerCount,
            'scoringCount' => $delivery->attempts->count(),
            'questionCount' => count($questions),
            'delivery' => $delivery,
            'tests' => Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)->get(),
            'groups' => Group::withoutGlobalScope(\App\Scopes\ClientScope::class)->get(),
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

    /**
     * Check if all questions in the attempt are scored and automatically finish scoring
     */
    private function checkAndFinishScoring(Attempt $attempt): void
    {
        // Skip if already finished
        if ($attempt->finish_scoring) {
            return;
        }

        $delivery = $attempt->delivery;
        if (!$delivery) {
            return;
        }

        // Get total question count for this exam
        $totalQuestions = \DB::table('questions')
            ->join('exam_item', 'questions.item_id', '=', 'exam_item.item_id')
            ->where('exam_item.exam_id', $delivery->exam_id)
            ->count();

        // Get scored question count for this attempt
        $scoredQuestions = \DB::table('attempt_question')
            ->join('questions', 'attempt_question.question_id', '=', 'questions.id')
            ->join('exam_item', 'questions.item_id', '=', 'exam_item.item_id')
            ->where('attempt_question.attempt_id', $attempt->id)
            ->where('exam_item.exam_id', $delivery->exam_id)
            ->whereNotNull('attempt_question.score')
            ->count();

        // If all questions are scored, automatically finish scoring
        if ($totalQuestions > 0 && $scoredQuestions >= $totalQuestions) {
            $attempt->finish_scoring = true;
            $attempt->save();
        }
    }
}
