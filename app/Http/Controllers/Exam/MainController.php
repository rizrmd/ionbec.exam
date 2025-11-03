<?php

namespace App\Http\Controllers\Exam;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Jobs\CalculateScore;
use App\Models\Exams\Answer;
use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use App\Models\Accounts\User;
use App\Models\Exams\Question;
use Dentro\Yalr\Attributes\Get;
use App\Models\Attempts\Attempt;
use Dentro\Yalr\Attributes\Post;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Attempts\AttemptQuestion;
use Veelasky\LaravelHashId\Rules\ExistsByHash;
use App\Services\RustService;

class MainController extends Controller
{
    #[Get('/exam', name: 'exam.main')]
    public function index(): \Illuminate\Http\RedirectResponse|\Inertia\Response
    {
        $dataSession = Session::get('exam');
        
        \Log::info('MainController: Session check', [
            'has_session' => $dataSession ? true : false,
            'has_delivery' => isset($dataSession['delivery']),
            'delivery_type' => isset($dataSession['delivery']) ? gettype($dataSession['delivery']) : 'not_set',
            'delivery_class' => isset($dataSession['delivery']) && is_object($dataSession['delivery']) ? get_class($dataSession['delivery']) : 'not_object'
        ]);
        
        // Check if session exists and has delivery
        if (!$dataSession || !isset($dataSession['delivery']) || !$dataSession['delivery']) {
            \Log::error('MainController: No session or delivery', [
                'dataSession' => $dataSession
            ]);
            return redirect('/')->with('error', 'Session expired. Please login again.');
        }
        
        // Get delivery ID handling both Eloquent and stdClass objects
        $deliveryId = null;
        $delivery = $dataSession['delivery'];
        
        \Log::info('MainController: Delivery object debug', [
            'delivery_type' => gettype($delivery),
            'delivery_class' => is_object($delivery) ? get_class($delivery) : null,
            'delivery_properties' => is_object($delivery) ? array_keys(get_object_vars($delivery)) : null,
            'has_id_property' => is_object($delivery) ? property_exists($delivery, 'id') : false,
            'delivery_attributes' => method_exists($delivery, 'getAttributes') ? array_keys($delivery->getAttributes()) : null
        ]);
        
        if (is_object($delivery)) {
            if (property_exists($delivery, 'id')) {
                $deliveryId = $delivery->id;
            } elseif (method_exists($delivery, 'getAttributes')) {
                // Try to get from Eloquent attributes
                $attributes = $delivery->getAttributes();
                if (isset($attributes['id'])) {
                    $deliveryId = $attributes['id'];
                }
            }
        }
        
        \Log::info('MainController: Extracted delivery ID', [
            'delivery_id' => $deliveryId
        ]);
        
        if (!$deliveryId) {
            \Log::error('MainController: Could not extract delivery ID');
            return redirect()->route('exam.finished')->with('error', 'Invalid delivery data');
        }
        
        // For both DEMO and normal sessions, reload delivery from database
        // Use withoutGlobalScope for DEMO which may not have proper client_id
        $delivery = Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('id', $deliveryId)
            ->first();
        
        \Log::info('MainController: Delivery query result', [
            'found' => $delivery ? true : false,
            'delivery_id' => $delivery ? $delivery->id : null
        ]);
        
        if (!$delivery) {
            \Log::error('MainController: Delivery not found in database', [
                'attempted_id' => $deliveryId
            ]);
            return redirect()->route('exam.finished')->with('error', 'Delivery not found');
        }

        // NOTE: Waiting room redirect logic removed
        // WaitingRoomController now handles the redirect to exam when time expires
        // This prevents redirect loop between waiting-room and exam

        // query taker and exam only when not in waiting-room.
        $takerId = null;
        if (isset($dataSession['taker']) && $dataSession['taker']) {
            $taker = $dataSession['taker'];
            if (is_object($taker)) {
                if (property_exists($taker, 'id')) {
                    $takerId = $taker->id;
                } elseif (method_exists($taker, 'getAttributes')) {
                    // Try to get from Eloquent attributes
                    $attributes = $taker->getAttributes();
                    if (isset($attributes['id'])) {
                        $takerId = $attributes['id'];
                    }
                }
            }
        }
        
        \Log::info('MainController: Taker extraction', [
            'has_taker' => isset($dataSession['taker']),
            'taker_id' => $takerId
        ]);
        
        $taker = $takerId ? Taker::query()->where('id', $takerId)->first() : null;
        
        \Log::info('MainController: Taker query result', [
            'found' => $taker ? true : false,
            'taker_id' => $taker ? $taker->id : null,
            'taker_email' => $taker ? $taker->email : null
        ]);
        
        // Get exam - first try relationship, then direct query
        $exam = $delivery->exam;
        
        \Log::info('MainController: Exam from delivery relationship', [
            'exam_found' => $exam ? true : false,
            'exam_id' => $exam ? $exam->id : null,
            'delivery_client_id' => $delivery->client_id ?? 'NULL'
        ]);
        
        // If relationship fails, try direct query (for DEMO with client_id issues)
        if (!$exam && $delivery->exam_id) {
            \Log::info('MainController: Trying direct exam query', [
                'exam_id' => $delivery->exam_id
            ]);
            $exam = Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('id', $delivery->exam_id)
                ->first();
                
            \Log::info('MainController: Direct exam query result', [
                'exam_found' => $exam ? true : false,
                'exam_id' => $exam ? $exam->id : null,
                'exam_name' => $exam ? $exam->name : null
            ]);
        }
        
        if (!$exam) {
            \Log::error('MainController: Exam not found for delivery', [
                'delivery_id' => $delivery->id,
                'exam_id' => $delivery->exam_id ?? 'NULL'
            ]);
            return redirect()->route('exam.finished')->with('error', 'Exam not found');
        }

        // Try Rust service for fast data loading, fallback to PHP
        $rustService = new RustService();
        $examData = $rustService->loadExamData(
            $exam->id, 
            $delivery->id, 
            $taker ? $taker->id : null
        );
        
        // Check if this is DEMO by checking taker email
        $isDemoSession = $taker && $taker->email === 'demo@example.com';
        
        if (($examData['success'] ?? false) && $isDemoSession) {
            // Use Rust-processed data with complete questions for DEMO
            $items = collect($examData['items']);
            \Log::info('DEMO: Using complete Rust data with questions', ['items_count' => $items->count()]);
            
            // Debug first question to check structure
            if ($items->count() > 0) {
                $firstItem = $items->first();
                $firstQuestion = $firstItem['questions'][0] ?? null;
                \Log::info('DEMO: First question debug', [
                    'question_type' => $firstQuestion['type'] ?? 'unknown',
                    'has_answers' => isset($firstQuestion['answers']) ? count($firstQuestion['answers']) : 0,
                    'question_id' => $firstQuestion['id'] ?? 'unknown',
                    'question_keys' => $firstQuestion ? array_keys($firstQuestion) : [],
                    'full_question' => $firstQuestion
                ]);
            }
        } else if ($examData['success'] ?? false) {
            // Use Rust-processed data for normal exams (lazy loading)
            $items = collect($examData['items']);
        } else {
            // Check if delivery has snapshot first
            $snapshot = $delivery->snapshot;

            if ($snapshot) {
                // Use snapshot data - ensures exam consistency
                \Log::info('MainController: Using delivery snapshot', [
                    'delivery_id' => $delivery->id,
                    'total_questions' => $snapshot->total_questions,
                    'total_items' => $snapshot->total_items,
                ]);

                $items = collect($snapshot->exam_structure['items'] ?? []);

                // Convert snapshot data to objects for compatibility
                $items = $items->map(function ($itemData) use ($isDemoSession) {
                    $item = (object) $itemData;

                    // Convert questions array
                    if (isset($item->questions) && is_array($item->questions)) {
                        $item->questions = collect($item->questions)->map(function ($questionData) use ($isDemoSession) {
                            $question = (object) $questionData;

                            // Convert answers array
                            if (isset($question->answers) && is_array($question->answers)) {
                                $question->answers = collect($question->answers)->map(function ($answerData) use ($isDemoSession) {
                                    $answer = (object) $answerData;
                                    // Hide correct answer for non-demo sessions
                                    if (!$isDemoSession) {
                                        unset($answer->is_correct_answer);
                                    }
                                    return $answer;
                                });
                            }

                            return $question;
                        });

                        // For DEMO, load all questions. For normal exam, use lazy loading
                        if (!$isDemoSession) {
                            $item->questions_count = count($itemData['questions'] ?? []);
                            $item->questions = collect([]); // Lazy loading
                        }
                    }

                    // Convert attachments array
                    if (isset($item->attachments) && is_array($item->attachments)) {
                        $item->attachments = collect($item->attachments)->map(function ($attachmentData) {
                            return (object) $attachmentData;
                        });
                    }

                    return $item;
                });
            } else if ($isDemoSession) {
                // DEMO fallback: Load complete question data from database
                $items = $exam->items()->with(['attachments', 'questions.answers'])->orderByPivot('order')->get();
                foreach ($items as $item) {
                    $item->questions_count = $item->questions()->count();
                    // Load complete questions for DEMO instead of empty collection
                    $item->questions = $item->questions()->with('answers')->get();
                    // Hide correct answers from frontend
                    $item->questions->each(function ($question) {
                        $question->answers->each(function ($answer) {
                            unset($answer->is_correct_answer);
                        });
                    });
                }
                \Log::info('DEMO: Using complete PHP fallback data', ['items_count' => $items->count()]);
            } else {
                // Normal fallback to PHP (lazy loading approach) - for old deliveries without snapshot
                \Log::warning('MainController: No snapshot found, using live exam data', [
                    'delivery_id' => $delivery->id,
                ]);
                $items = $exam->items()->with(['attachments'])->orderByPivot('order')->get();
                foreach ($items as $item) {
                    $item->questions_count = $item->questions()->count();
                    $item->questions = collect();
                }
            }
        }

        // Calculate remaining time in seconds (timezone agnostic)
        $remainingSeconds = 0;
        if ($delivery->automatic_start) {
            // Handle DEMO delivery objects where scheduled_at might be a string
            $scheduledAt = is_string($delivery->scheduled_at) 
                ? Carbon::parse($delivery->scheduled_at) 
                : $delivery->scheduled_at;
            
            $endTime = $scheduledAt->copy()->addMinutes($delivery->duration);
            $remainingSeconds = max(0, $endTime->diffInSeconds(Carbon::now()));
        } else if ($isDemoSession && $delivery->duration) {
            // For DEMO sessions without automatic_start, give them the full duration
            \Log::info('DEMO: Setting full duration for non-automatic start', [
                'duration_minutes' => $delivery->duration
            ]);
            $remainingSeconds = $delivery->duration * 60; // Convert minutes to seconds
        }

        $data = [
            'delivery' => $delivery,
            'taker' => $taker,
            'exam' => $exam,
            'examItems' => $items,
            'remainingSeconds' => $remainingSeconds,
        ];

        if ($dataSession['admin']) {
            $data['admin'] = User::byHash($dataSession['admin']);
        }

        if (null !== $taker) {
            // Create real database attempt for both DEMO and normal sessions
            $attempt = Attempt::query()
                ->where('attempted_by', $taker->id)
                ->where('exam_id', $exam->id)
                ->where('delivery_id', $delivery->id)
                ->first();

            if (! $attempt) {
                $attempt = new Attempt();
                $attempt->attempted_by = $taker->id;
                $attempt->exam_id = $exam->id;
                $attempt->delivery_id = $delivery->id;
                $attempt->ip_address = \Illuminate\Support\Facades\Request::ip();
                $attempt->started_at = Carbon::now();
                $attempt->save();
            }

            $data['attempt'] = $attempt;
            $data['attemptQuestions'] = AttemptQuestion::query()->with('question')->where('attempt_id', $attempt->id)->get();
            
            // For non-automatic start with existing attempt, calculate remaining time from attempt start
            // This overrides the default duration set for new DEMO sessions
            if (!$delivery->automatic_start && !$attempt->wasRecentlyCreated) {
                $endTime = $attempt->started_at->copy()->addMinutes($delivery->duration);
                $remainingSeconds = max(0, $endTime->diffInSeconds(Carbon::now()));
                $data['remainingSeconds'] = $remainingSeconds;
                \Log::info('Calculated remaining time from existing attempt', [
                    'attempt_id' => $attempt->id,
                    'started_at' => $attempt->started_at,
                    'remaining_seconds' => $remainingSeconds
                ]);
            }
        }

        \Log::info('MainController: Rendering exam interface', [
            'has_delivery' => isset($data['delivery']),
            'has_taker' => isset($data['taker']),
            'has_exam' => isset($data['exam']),
            'items_count' => isset($data['examItems']) ? count($data['examItems']) : 0,
            'has_attempt' => isset($data['attempt']),
            'remaining_seconds' => $data['remainingSeconds'] ?? 0
        ]);
        
        return Inertia::render('Exam/Main', $data);
    }

    #[Get('/exam/questions/{item_hash}', name: 'exam.get-taker-answer')]
    public function getQuestions(Item $item): \Illuminate\Http\JsonResponse
    {
        $dataSession = Session::get('exam');
        $questionQuery = Question::query()->where('item_id', $item->id)->with('answers');
        $questions = $questionQuery->clone()->get();
        $questionsId = $questionQuery->pluck('id');

        $attempt = null;
        if ($dataSession['taker'] && $dataSession['delivery']) {
            // Handle both Eloquent and stdClass objects
            $deliveryId = is_object($dataSession['delivery']) && property_exists($dataSession['delivery'], 'id') 
                ? $dataSession['delivery']->id 
                : null;
                
            if ($deliveryId) {
                $delivery = Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('id', $deliveryId)
                    ->first();
                    
                // Try relationship first, then direct query
                $exam = $delivery ? $delivery->exam : null;
                if (!$exam && $delivery && $delivery->exam_id) {
                    $exam = Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
                        ->where('id', $delivery->exam_id)
                        ->first();
                }
                
                if ($exam) {
                    // Handle taker ID extraction
                    $takerId = is_object($dataSession['taker']) && property_exists($dataSession['taker'], 'id')
                        ? $dataSession['taker']->id
                        : null;
                        
                    if ($takerId) {
                        $attempt = Attempt::query()
                            ->where('attempted_by', $takerId)
                            ->where('exam_id', $exam->id)
                            ->with('questions', function ($query) use ($questionsId) {
                                $query->whereIn('question_id', $questionsId);
                            })
                            ->latest()->first();
                    }
                }
            }
        }

        //        $item->load('attachments');

        //        hiding is_correct_answer column
        $questions->each(function ($question, $questionKey) use ($questions) {
            $questions[$questionKey]->answers->each(function ($answer, $answerKey) use ($questions, $questionKey) {
                unset($questions[$questionKey]->answers[$answerKey]->is_correct_answer);
            });
        });

        return response()->json($attempt);
    }

    #[Post('/exam/answer', name: 'exam.answer')]
    public function answer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'attempt_hash' => ['required', new ExistsByHash(Attempt::class)],
            'answers_value' => 'array',
        ]);

        $answerResults = [];
        $attempt = Attempt::byHash($request->attempt_hash);
        if (count($request->answers_value) >= 1) {
            foreach ($request->answers_value as $questionHash => $answerValue) {
                $question = Question::byHash($questionHash);
                $answer = (null !== $question->type && 'multiple-choice' === $question->type->name) ? Answer::byHash($answerValue) : $answerValue;
                $score = (null !== $question->type && 'multiple-choice' === $question->type->name && $answer->is_correct_answer) ? $question->score : 0;

                AttemptQuestion::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'answer_id' => (null !== $question->type && 'multiple-choice' === $question->type->name) ? $answer->id : null,
                        'answer_hash' => (null !== $question->type && 'multiple-choice' === $question->type->name) ? Answer::idToHash($answer->id) : null,
                        'answer' => (null !== $question->type && 'multiple-choice' === $question->type->name) ? $answer->answer : $answer,
                        'score' => $score,
                        'is_correct' => 0 !== $score,
                    ]
                );
            }
        }

        $this->calculateScore($attempt);

        return response()->json($answerResults);
    }

    private function calculateScore($attempt)
    {
        $this->dispatch(new CalculateScore($attempt));
    }
}
