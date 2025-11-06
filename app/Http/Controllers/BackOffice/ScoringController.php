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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ScoringController extends Controller
{
    #[Get('back-office/scoring', name: 'back-office.scoring.index')]
    public function index(Request $request): Response
    {
        $deliveries = Delivery::query()
            ->select('id', 'name', 'scheduled_at', 'exam_id', 'group_id', 'hash', 'duration', 'automatic_start', 'is_anytime', 'last_status')
            ->orderBy('scheduled_at', 'DESC');

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
        
        // Manually load exam and group relationships to bypass ClientScope issues
        $paginatedDeliveries->getCollection()->transform(function ($delivery) {
            $exam = \App\Models\Exams\Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->select('id', 'name', 'is_interview')
                ->where('id', $delivery->exam_id)
                ->first();
            $delivery->setRelation('exam', $exam);
            
            $group = \App\Models\Takers\Group::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->select('id', 'name', 'hash')
                ->where('id', $delivery->group_id)
                ->first();
            if ($group) {
                $group->makeVisible(['id']);
            }
            $delivery->setRelation('group', $group);
            
            // Calculate takers_count
            $takersCount = 0;
            if ($group) {
                $takersCount = DB::table('group_taker')
                    ->where('group_id', $group->id)
                    ->count();
            }
            $delivery->takers_count = $takersCount;
            
            // Calculate questions_count
            $questionsCount = 0;
            if ($exam) {
                $questionsCount = DB::table('questions')
                    ->join('exam_item', 'questions.item_id', '=', 'exam_item.item_id')
                    ->where('exam_item.exam_id', $exam->id)
                    ->count();
            }
            $delivery->questions_count = $questionsCount;
            
            return $delivery;
        });

        return Inertia::render('BackOffice/Scoring/Index', [
            'payload' => $paginatedDeliveries,
            'tests' => Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)->select('id', 'name', 'hash')->get(),
            'groups' => Group::withoutGlobalScope(\App\Scopes\ClientScope::class)->select('id', 'name', 'hash')->get(),
        ]);
    }

    #[Get('back-office/scoring/{delivery_hash}', name: 'back-office.scoring.detail')]
    public function detail(Request $request, Delivery $delivery): Response
    {
        // Use database query optimization instead of eager loading everything
        $cacheKey = 'scoring_detail_' . $delivery->id . '_' . md5(serialize($request->all()));
        
        $data = (function () use ($request, $delivery) {
            // Get counts using optimized queries
            $takerCount = DB::table('group_taker')
                ->where('group_id', $delivery->group_id)
                ->count();
            
            $scoringCount = DB::table('attempts')
                ->where('delivery_id', $delivery->id)
                ->count();
            
            // Get question count with a single query
            $questionCount = DB::table('questions')
                ->join('exam_item', 'questions.item_id', '=', 'exam_item.item_id')
                ->where('exam_item.exam_id', $delivery->exam_id)
                ->count();

            // Get all takers in the group
            $groupTakerIds = DB::table('group_taker')
                ->where('group_id', $delivery->group_id)
                ->pluck('taker_id');

            // Get existing attempts
            $existingAttempts = Attempt::query()
                ->select('id', 'delivery_id', 'exam_id', 'attempted_by', 'score', 'progress', 'started_at', 'ended_at', 'hash', 'finish_scoring')
                ->where('delivery_id', $delivery->id)
                ->where('exam_id', $delivery->exam_id)
                ->with(['taker:id,name,email'])
                ->get();

            // Create a collection that includes all attempts (both from group members and others)
            $allAttempts = collect();
            
            // First, add all existing attempts (including those from non-group members)
            foreach ($existingAttempts as $existingAttempt) {
                // Check if this attempt's taker is in the group_taker table
                $pivotData = DB::table('group_taker')
                    ->where('group_id', $delivery->group_id)
                    ->where('taker_id', $existingAttempt->attempted_by)
                    ->first();
                
                if (!$pivotData) {
                    // This is an orphaned attempt - convert to array and set custom taker_code
                    $groupData = DB::table('groups')->where('id', $delivery->group_id)->first();
                    $groupCode = $groupData ? $groupData->code : 'UNKNOWN';
                    $customTakerCode = $groupCode . '-ORPHAN-' . str_pad($existingAttempt->attempted_by, 3, '0', STR_PAD_LEFT);
                    
                    // Convert to object and override taker_code
                    $attemptArray = $existingAttempt->toArray();
                    $attemptArray['taker_code'] = $customTakerCode;
                    $allAttempts->push((object) $attemptArray);
                } else {
                    // Normal attempt - add as is
                    $allAttempts->push($existingAttempt);
                }
            }
            
            // Then, add placeholders for group members who haven't attempted
            foreach ($groupTakerIds as $takerId) {
                $hasAttempt = $existingAttempts->where('attempted_by', $takerId)->count() > 0;
                
                if (!$hasAttempt) {
                    // Create a placeholder attempt for takers who haven't started
                    $taker = \App\Models\Takers\Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
                        ->select('id', 'name', 'email')
                        ->find($takerId);
                    
                    if ($taker) {
                        // Get the group code directly using DB query to avoid ClientScope issues
                        $groupData = DB::table('groups')->where('id', $delivery->group_id)->first();
                        $groupCode = $groupData ? $groupData->code : 'UNKNOWN';
                        
                        $takerCode = \App\Models\Attempts\Attempt::getFormattedTakerCode(
                            $takerId,
                            $delivery->group_id,
                            $groupCode
                        );
                        
                        // Create placeholder as array instead of Eloquent model to avoid hash issues
                        $placeholder = (object) [
                            'id' => null,
                            'delivery_id' => $delivery->id,
                            'exam_id' => $delivery->exam_id,
                            'attempted_by' => $takerId,
                            'score' => 0,
                            'progress' => 0,
                            'started_at' => null,
                            'ended_at' => null,
                            'created_at' => null,
                            'hash' => null,
                            'finish_scoring' => false,
                            'taker' => $taker,
                            'taker_code' => $takerCode, // Use proper formatted taker code
                            'is_placeholder' => true // Flag to identify placeholders
                        ];
                        $allAttempts->push($placeholder);
                    }
                }
            }

            // Apply search filter if provided
            if ($request->input('query')) {
                $queryString = $request->input('query');
                $allAttempts = $allAttempts->filter(function ($attempt) use ($queryString) {
                    return $attempt->taker && (
                        stripos($attempt->taker->email, $queryString) !== false ||
                        stripos($attempt->taker->name, $queryString) !== false
                    );
                });
            }

            // Manual pagination
            $perPage = $request->input('perPage', 15);
            $currentPage = $request->input('page', 1);
            $total = $allAttempts->count();
            $offset = ($currentPage - 1) * $perPage;
            $items = $allAttempts->slice($offset, $perPage)->values();

            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );
            $paginated->withQueryString();

            return [
                'takerCount' => $takerCount,
                'scoringCount' => $scoringCount,
                'questionCount' => $questionCount,
                'attempts' => $paginated
            ];
        })();

        // Load delivery data separately (minimal fields) - fix for ClientScope
        $exam = $delivery->exam;
        $group = $delivery->group;
        
        // Manually set relationships for frontend serialization
        if ($exam) {
            $delivery->setRelation('exam', $exam);
        }
        if ($group) {
            $delivery->setRelation('group', $group);
        }

        return Inertia::render('BackOffice/Scoring/Detail', array_merge($data, [
            'delivery' => $delivery,
            'payload' => $data['attempts']
        ]));
    }

    #[Get('back-office/scoring/{delivery_hash}/pdf/{attempt_hash}', name: 'back-office.scoring.pdf')]
    public function scoringPdf(Request $request, Delivery $delivery, Attempt $attempt): Response
    {
        // Cache this heavy query
        $cacheKey = 'scoring_pdf_' . $attempt->id;
        
        $data = Cache::remember($cacheKey, 300, function () use ($delivery, $attempt) {
            $attemptQuests = AttemptQuestion::query()
                ->with('question:id,content,type')
                ->where('attempt_id', $attempt->id)
                ->get();
            
            $exam = Exam::query()
                ->with([
                    'items' => function ($q) {
                        $q->orderBy('order', 'DESC');
                    },
                    'items.questions.answers',
                    'items.attachments',
                ])
                ->where('id', $delivery->exam_id)
                ->first();
            
            return [
                'attemptQuests' => $attemptQuests,
                'exam' => $exam
            ];
        });

        return Inertia::render('BackOffice/Scoring/PDF', array_merge($data, [
            'taker' => $attempt->taker,
            'takerCode' => $attempt->taker_code,
            'itemTypes' => ItemType::getOptions(),
            'delivery' => $delivery,
        ]));
    }

    #[Get('back-office/scoring/{delivery_hash}/exam/{attempt_hash}', name: 'back-office.scoring.exam')]
    public function scoringShow(Request $request, Delivery $delivery, Attempt $attempt): Response
    {
        try {
            // Validate data integrity
            if (!$delivery) {
                throw new \Exception('Delivery not found');
            }

            if (!$attempt) {
                throw new \Exception('Attempt not found');
            }

            if ($attempt->delivery_id !== $delivery->id) {
                throw new \Exception('Attempt does not belong to this delivery');
            }

            // Load data with fallbacks
            $data = $this->loadScoringData($delivery, $attempt);

            return Inertia::render('BackOffice/Scoring/Exam', $data);

        } catch (\Exception $e) {
            // Log error and show user-friendly message
            \Log::error('Scoring page error: ' . $e->getMessage());

            return Inertia::render('BackOffice/Scoring/Exam', [
                'error' => 'Unable to load scoring data: ' . $e->getMessage(),
                'attempt' => $attempt,
                'delivery' => $delivery,
                'items' => collect(),
                'attemptQuests' => collect(),
                'takerCode' => $attempt->taker_code ?? 'UNKNOWN',
                'isInterview' => $delivery->is_interview ?? false,
                'hasAttemptQuestions' => false,
            ]);
        }
    }

    #[Post('back-office/scoring/submit-score', name: 'back-office.scoring.scoring-submit')]
    public function submitScore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'attempt_hash' => ['required', new ExistsByHash(Attempt::class)],
                'corrects' => 'required|array',
                'scores' => 'required|array',
            ]);

            $attempt = Attempt::byHash($request->attempt_hash);

            // Use upsert instead of update - creates if not exists
            DB::transaction(function () use ($request, $attempt) {
                foreach ($request->corrects as $questionHash => $correct) {
                    $question = Question::byHash($questionHash);

                    // Use updateOrCreate to create or update attempt_question
                    AttemptQuestion::updateOrCreate(
                        [
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                        ],
                        [
                            'score' => $request->scores[$questionHash] ?? 0,
                            'is_correct' => $correct,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });

            // Clear related caches
            $this->clearScoringCache($attempt);

            return response()->json([
                'success' => true,
                'attempt' => $attempt->fresh(['attemptQuestions']),
                'message' => 'Scores submitted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Submit score error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit scores: ' . $e->getMessage()
            ], 500);
        }
    }

    private function clearScoringCache($attempt)
    {
        Cache::forget('scoring_detail_' . $attempt->delivery_id . '_*');
        Cache::forget('scoring_exam_' . $attempt->id);
        Cache::forget('scoring_pdf_' . $attempt->id);
    }

    /**
     * Load scoring data with fallbacks for empty data
     */
    private function loadScoringData($delivery, $attempt): array
    {
        try {
            // Load delivery relationships properly - bypass ClientScope
            $delivery->load(['group:id,name', 'exam:id,name']);

            // Debug logging
            \Log::info("Loading scoring data", [
                'delivery_id' => $delivery->id,
                'exam_id' => $delivery->exam_id,
                'attempt_id' => $attempt->id,
                'delivery_name' => $delivery->name,
                'exam_name' => $delivery->exam->name ?? 'Unknown'
            ]);

            // Always load items independently from attempt data - bypass ClientScope
            $items = Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->whereHas('exams', function ($q) use ($delivery) {
                    $q->where('id', $delivery->exam_id);
                })
                ->with(['questions.answers', 'attachments'])
                ->get();

            \Log::info("Items query result (with ClientScope bypass)", [
                'items_count' => $items->count(),
                'exam_id' => $delivery->exam_id,
                'client_id' => $delivery->client_id
            ]);

            // If items empty, try direct query and convert to proper models
            if ($items->isEmpty()) {
                \Log::warning("Items empty, trying direct query");

                $itemsData = \DB::table('items')
                    ->join('exam_item', 'items.id', '=', 'exam_item.item_id')
                    ->where('exam_item.exam_id', $delivery->exam_id)
                    ->get()
                    ->toArray();

                \Log::info("Direct query result", [
                    'items_count' => count($itemsData),
                    'sample' => array_slice($itemsData, 0, 3)
                ]);

                // Convert to Item models - bypass ClientScope
                $items = collect();
                foreach ($itemsData as $itemData) {
                    // Get the actual Item model with relationships - bypass ClientScope
                    $itemModel = \App\Models\Exams\Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
                        ->with(['questions.answers', 'attachments'])
                        ->find($itemData->id);
                    if ($itemModel) {
                        $items->push($itemModel);
                    } else {
                        \Log::warning("Failed to load item model", ['item_id' => $itemData->id]);
                    }
                }

                \Log::info("Converted models count", ['items_count' => $items->count()]);
            }

            // Load attempt questions (may be empty)
            $attemptQuests = AttemptQuestion::query()
                ->with('question:id,question,type')
                ->where('attempt_id', $attempt->id)
                ->get();

            // Generate proper taker code if missing
            $takerCode = $attempt->taker_code;
            if (empty($takerCode)) {
                try {
                    $takerCode = \App\Models\Attempts\Attempt::getFormattedTakerCode(
                        $attempt->attempted_by,
                        $delivery->group_id,
                        $delivery->group->code ?? 'UNKNOWN'
                    );
                    \Log::info("Generated taker code", [
                        'attempt_id' => $attempt->id,
                        'taker_id' => $attempt->attempted_by,
                        'group_id' => $delivery->group_id,
                        'group_code' => $delivery->group->code,
                        'generated_code' => $takerCode
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Failed to generate taker code", [
                        'error' => $e->getMessage(),
                        'attempt_id' => $attempt->id
                    ]);
                    $takerCode = 'UNKNOWN-' . str_pad($attempt->attempted_by, 3, '0', STR_PAD_LEFT);
                }
            }

            return [
                'attempt' => $attempt,
                'attemptQuests' => $attemptQuests,
                'items' => $items,
                'hasAttemptQuestions' => $attemptQuests->isNotEmpty(),
                'takerCode' => $takerCode,
                'isInterview' => $delivery->is_interview ?? false,
                'delivery' => $delivery,
            ];

        } catch (\Exception $e) {
            \Log::error('Error loading scoring data: ' . $e->getMessage());

            // Return minimal data structure to prevent crashes
            return [
                'attempt' => $attempt,
                'attemptQuests' => collect(),
                'items' => collect(),
                'hasAttemptQuestions' => false,
                'takerCode' => $attempt->taker_code ?? 'UNKNOWN',
                'isInterview' => $delivery->is_interview ?? false,
                'delivery' => $delivery,
                'error' => 'Failed to load some data: ' . $e->getMessage(),
            ];
        }
    }
}