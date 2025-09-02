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
        // Cache the query for 5 minutes
        $cacheKey = 'scoring_index_' . md5(serialize($request->all()));
        
        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            $deliveries = Delivery::query()
                ->select('id', 'name', 'scheduled_at', 'exam_id', 'group_id', 'hash')
                ->with([
                    'exam:id,name',
                    'group:id,name'
                ])
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

            return $deliveries->paginate($request->input('perPage', 15))->withQueryString();
        });

        // Cache tests and groups separately (longer cache time)
        $tests = Cache::remember('all_exams', 3600, fn() => Exam::select('id', 'name', 'hash')->get());
        $groups = Cache::remember('all_groups', 3600, fn() => Group::select('id', 'name', 'hash')->get());

        return Inertia::render('BackOffice/Scoring/Index', [
            'payload' => $data,
            'tests' => $tests,
            'groups' => $groups,
        ]);
    }

    #[Get('back-office/scoring/{delivery_hash}', name: 'back-office.scoring.detail')]
    public function detail(Request $request, Delivery $delivery): Response
    {
        // Use database query optimization instead of eager loading everything
        $cacheKey = 'scoring_detail_' . $delivery->id . '_' . md5(serialize($request->all()));
        
        $data = Cache::remember($cacheKey, 60, function () use ($request, $delivery) {
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

            // Get paginated attempts with minimal data
            $attempts = Attempt::query()
                ->select('id', 'delivery_id', 'exam_id', 'taker_id', 'score', 'started_at', 'ended_at', 'hash')
                ->where('delivery_id', $delivery->id)
                ->where('exam_id', $delivery->exam_id);
            
            $attempts->when($request->input('query') ?? false, function ($query, $queryString) {
                $query->whereHas('taker', function ($q) use ($queryString) {
                    $q->where('email', 'like', "%{$queryString}%");
                });
                $query->orWherehas('delivery.group.takers', function ($q) use ($queryString) {
                    $q->where('taker_code', 'like', "%{$queryString}%");
                });
            });

            return [
                'takerCount' => $takerCount,
                'scoringCount' => $scoringCount,
                'questionCount' => $questionCount,
                'attempts' => $attempts->with(['taker:id,name,email'])
                    ->paginate($request->input('perPage', 15))
                    ->withQueryString()
            ];
        });

        // Load delivery data separately (minimal fields)
        $delivery->load([
            'exam:id,name',
            'group:id,name'
        ]);

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