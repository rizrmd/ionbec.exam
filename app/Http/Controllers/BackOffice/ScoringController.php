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
        // Optimize with selective loading
        $cacheKey = 'scoring_exam_' . $attempt->id;
        
        $data = Cache::remember($cacheKey, 300, function () use ($delivery, $attempt) {
            $attempt->load('delivery.group:id,name');
            
            $attemptQuests = AttemptQuestion::query()
                ->with('question:id,content,type')
                ->where('attempt_id', $attempt->id)
                ->get();
            
            $items = Item::query()
                ->whereHas('exams', function ($q) use ($delivery) {
                    $q->where('id', $delivery->exam_id);
                })
                ->with(['questions.answers', 'attachments'])
                ->get();
            
            return [
                'attempt' => $attempt,
                'attemptQuests' => $attemptQuests,
                'items' => $items
            ];
        });

        return Inertia::render('BackOffice/Scoring/Exam', array_merge($data, [
            'takerCode' => $attempt->taker_code,
            'isInterview' => $delivery->is_interview,
        ]));
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

        // Use bulk update for better performance
        $updates = [];
        foreach ($request->corrects as $key => $correct) {
            $question = Question::byHash($key);
            $updates[] = [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'score' => $request->scores[$key],
                'is_correct' => $correct,
            ];
        }

        // Batch update
        DB::transaction(function () use ($updates, $attempt) {
            foreach ($updates as $update) {
                AttemptQuestion::where([
                    ['attempt_id', $update['attempt_id']],
                    ['question_id', $update['question_id']],
                ])->update([
                    'score' => $update['score'],
                    'is_correct' => $update['is_correct']
                ]);
            }
        });

        // Clear related caches
        Cache::forget('scoring_detail_' . $attempt->delivery_id . '_*');
        Cache::forget('scoring_exam_' . $attempt->id);
        Cache::forget('scoring_pdf_' . $attempt->id);

        return response()->json([
            'attempt' => $attempt,
        ]);
    }
}