<?php

namespace App\Http\Controllers\Exam;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Jobs\CalculateScore;
use App\Models\Exams\Answer;
use App\Models\Takers\Taker;
use App\Services\ExamTimerService;
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

        // 🔒 NEW: Enhanced session validation
        if (!$dataSession || !is_array($dataSession)) {
            \Log::error('MainController: Invalid session structure', [
                'session_exists' => isset($dataSession),
                'session_type' => gettype($dataSession),
                'session_data' => $dataSession
            ]);
            return redirect('/')->with('error', 'Invalid session data. Please login again.');
        }

        \Log::info('MainController: Session validation', [
            'has_session' => true,
            'session_keys' => array_keys($dataSession),
            'has_delivery' => isset($dataSession['delivery']),
            'delivery_type' => isset($dataSession['delivery']) ? gettype($dataSession['delivery']) : 'not_set',
            'delivery_class' => isset($dataSession['delivery']) && is_object($dataSession['delivery']) ? get_class($dataSession['delivery']) : 'not_object'
        ]);

        // Check if session exists and has delivery
        if (!isset($dataSession['delivery']) || !$dataSession['delivery']) {
            \Log::error('MainController: No delivery in session', [
                'session_keys' => array_keys($dataSession)
            ]);
            return redirect('/')->with('error', 'Delivery not found in session. Please login again.');
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

        // UNIFIED APPROACH: Force use Rust API for consistency to resolve green indicator issues
        // This prevents conflicts between snapshot data (no hashes), database data (different hashes), and Rust API (complete hashes)
        $rustService = new RustService();
        $examData = $rustService->loadExamData(
            $exam->id,
            $delivery->id,
            $taker ? $taker->id : null
        );

        \Log::info('UNIFIED DATA SOURCE: Using Rust API to resolve hash conflicts', [
            'exam_id' => $exam->id,
            'delivery_id' => $delivery->id,
            'rust_success' => $examData['success'] ?? false,
            'rust_items_count' => isset($examData['items']) ? count($examData['items']) : 0
        ]);

        if (($examData['success'] ?? false) && isset($examData['items'])) {
            // UNIFIED: Always use Rust API data for both DEMO and normal sessions
            $items = collect($examData['items']);
            \Log::info('RUST API: Using unified data source', ['items_count' => $items->count()]);

            // Debug first item structure to verify hashes are present
            if ($items->count() > 0) {
                $firstItem = $items->first();
                $firstQuestion = $firstItem['questions'][0] ?? null;
                \Log::info('RUST API: Item structure verification', [
                    'item_hash' => $firstItem['hash'] ?? 'MISSING',
                    'item_name' => $firstItem['name'] ?? 'NO NAME',
                    'question_hash' => $firstQuestion['hash'] ?? 'MISSING',
                    'has_answers' => isset($firstQuestion['answers']) ? count($firstQuestion['answers']) : 0,
                    'rust_api_working' => true
                ]);
            }
        } else {
            \Log::error('RUST API FAILED: Critical issue - Rust service unavailable', [
                'exam_id' => $exam->id,
                'delivery_id' => $delivery->id,
                'rust_response' => $examData
            ]);

            // EMERGENCY FALLBACK: Use snapshot if Rust fails completely
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

        // 🔒 Initialize attempt variable to prevent undefined variable error
        $attempt = null;

        // 🔒 UNIFIED TIMER: Use single timer calculation method
        $remainingSeconds = app(ExamTimerService::class)->calculateRemainingSeconds($delivery, $attempt);

        // 🔒 NEW: Enhanced data validation and structure fix
        \Log::info('MainController: Preparing frontend data structure', [
            'delivery_id' => $delivery->id ?? null,
            'exam_id' => $exam->id ?? null,
            'taker_id' => $taker->id ?? null,
            'items_count' => $items ? $items->count() : 0
        ]);

        $data = [
            'delivery' => $delivery,
            'taker' => $taker,
            'exam' => $exam,
            'examItems' => $items,
            'remainingSeconds' => $remainingSeconds,
            // 🔒 CRITICAL FIX: Add explicit IDs for frontend context
            'examId' => $exam->id ?? null,
            'deliveryId' => $delivery->id ?? null,
            'takerId' => $taker->id ?? null,
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
            $data['attemptQuestions'] = $attempt->questions;

            // CRITICAL FIX: Add item_hash to initial attemptQuestions for onMounted matching
            if ($data['attemptQuestions'] && $data['attemptQuestions']->count() > 0) {
                $data['attemptQuestions']->each(function ($question) use ($items) {
                    // Find corresponding item by checking question-item relationship directly
                    $matchingItem = $items->first(function ($item) use ($question) {
                        // CRITICAL FIX: Rust API items are objects, not arrays - use object property access with fallback
                        $itemId = $item->id ?? $item['id'] ?? null;
                        if (!$itemId) {
                            return false;
                        }

                        // Load the questions for this item to check relationship
                        $itemQuestions = \App\Models\Exams\Question::where('item_id', $itemId)->get();
                        return $itemQuestions->contains(function ($q) use ($question) {
                            return $q->id === $question->id;
                        });
                    });

                    if ($matchingItem) {
                        // CRITICAL FIX: Use object property access with fallback for Rust API data
                        $question->item_hash = $matchingItem->hash ?? $matchingItem['hash'] ?? null;
                        \Log::info('Added item hash to initial attempt question', [
                            'question_id' => $question->id,
                            'question_hash' => $question->hash,
                            'item_hash' => $question->item_hash,
                            'answer_hash' => $question->pivot->answer_hash ?? 'N/A'
                        ]);
                    }
                });
            }
            
        }

        // 🔒 NEW: Final validation of data structure before sending to frontend
        \Log::info('MainController: Final data validation before frontend render', [
            'exam_id' => $data['examId'],
            'delivery_id' => $data['deliveryId'],
            'taker_id' => $data['takerId'],
            'exam_items_valid' => $items ? $items->count() : 0,
            'attempt_questions_count' => isset($data['attemptQuestions']) ? $data['attemptQuestions']->count() : 0,
            'data_structure_valid' => !empty($data['examId']) && !empty($data['deliveryId'])
        ]);

        // Validate items structure before sending to frontend
        if ($items) {
            $items->each(function ($item, $index) {
                $itemHash = $item->hash ?? $item['hash'] ?? null;
                $itemName = $item->name ?? $item['name'] ?? 'ITEM_' . $index;
                \Log::info('Item validation before frontend', [
                    'index' => $index,
                    'item_hash' => $itemHash,
                    'item_name' => $itemName,
                    'has_questions' => isset($item->questions) || isset($item['questions'])
                ]);
            });
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
    public function getQuestions($item_hash): \Illuminate\Http\JsonResponse
    {
        $dataSession = Session::get('exam');

        \Log::info('getQuestions called', [
            'item_hash' => $item_hash,
            'item_hash_type' => gettype($item_hash)
        ]);

        // Handle different hash formats - frontend might send nested object or string
        if (is_string($item_hash)) {
            $actualHash = $item_hash;
            \Log::info('Processing string hash', ['hash' => $actualHash]);
        } elseif (is_object($item_hash)) {
            // Handle object serialization - could be nested or flat structure
            $jsonString = json_encode($item_hash);
            $assocArray = json_decode($jsonString, true);

            \Log::info('Debugging object hash', [
                'original_item_hash_type' => get_class($item_hash),
                'json_string' => $jsonString,
                'assoc_array_keys' => array_keys($assocArray),
                'has_direct_hash' => isset($assocArray['hash'])
            ]);

            // Check for direct hash property first (flat structure)
            if (isset($assocArray['hash'])) {
                $actualHash = $assocArray['hash'];
                \Log::info('Processing direct hash property', ['extracted_hash' => $actualHash]);
            }
            // Check for nested structure: {"App\\Models\\Exams\\Item": {"hash": "abc"}}
            elseif (is_array($assocArray) && count($assocArray) > 0) {
                $modelClass = array_key_first($assocArray);

                if ($modelClass && isset($assocArray[$modelClass]['hash'])) {
                    $actualHash = $assocArray[$modelClass]['hash'];
                    \Log::info('Processing nested object hash', [
                        'model_class' => $modelClass,
                        'extracted_hash' => $actualHash
                    ]);
                } else {
                    \Log::error('Invalid object format - no hash found in any structure', [
                        'model_class' => $modelClass,
                        'assoc_array' => $assocArray
                    ]);
                    return response()->json([
                        'error' => 'Invalid hash format - no hash found',
                        'questions' => [],
                        'attempt' => null
                    ], 400);
                }
            } else {
                \Log::error('Failed to decode object to array', [
                    'item_hash' => $item_hash,
                    'json_decode_result' => $assocArray
                ]);
                return response()->json([
                    'error' => 'Invalid hash format - cannot process object',
                    'questions' => [],
                    'attempt' => null
                ], 400);
            }
        } else {
            \Log::error('Invalid hash format', [
                'item_hash' => $item_hash,
                'type' => gettype($item_hash)
            ]);
            return response()->json([
                'error' => 'Invalid hash format',
                'questions' => [],
                'attempt' => null
            ], 400);
        }

        // CRITICAL FIX: Extract session data the same way as index() method
        // Use proper session structure extraction
        $examId = null;
        $deliveryId = null;
        $takerId = null;

        // Extract delivery ID with same logic as index()
        if (isset($dataSession['delivery'])) {
            $delivery = $dataSession['delivery'];
            if (is_object($delivery) && method_exists($delivery, 'getId')) {
                $deliveryId = $delivery->getId();
            } elseif (is_object($delivery) && isset($delivery->id)) {
                $deliveryId = $delivery->id;
            } elseif (is_array($delivery) && isset($delivery['id'])) {
                $deliveryId = $delivery['id'];
            }
        }

        // Extract taker ID with same logic as index()
        if (isset($dataSession['taker'])) {
            $taker = $dataSession['taker'];
            if (is_object($taker) && method_exists($taker, 'getId')) {
                $takerId = $taker->getId();
            } elseif (is_object($taker) && isset($taker->id)) {
                $takerId = $taker->id;
            } elseif (is_array($taker) && isset($taker['id'])) {
                $takerId = $taker['id'];
            }
        }

        // Extract exam ID - get from delivery relationship
        if ($deliveryId) {
            $delivery = \App\Models\Deliveries\Delivery::find($deliveryId);
            if ($delivery) {
                $examId = $delivery->exam_id;
            }
        }

        \Log::info('getQuestions: Session extraction', [
            'delivery_id' => $deliveryId,
            'exam_id' => $examId,
            'taker_id' => $takerId
        ]);

        // UNIFIED APPROACH: Try Rust API first to maintain hash consistency
        $rustService = new RustService();
        $examData = $rustService->loadExamData(
            $examId,
            $deliveryId,
            $takerId
        );

        \Log::info('getQuestions: Rust API fallback attempt', [
            'hash' => $actualHash,
            'rust_success' => $examData['success'] ?? false,
            'rust_items_count' => isset($examData['items']) ? count($examData['items']) : 0
        ]);

        if (($examData['success'] ?? false) && isset($examData['items'])) {
            // Find item in Rust data by hash
            $rustItem = null;
            $rustQuestions = [];

            foreach ($examData['items'] as $item) {
                if (($item['hash'] ?? '') === $actualHash) {
                    $rustItem = $item;
                    $rustQuestions = $item['questions'] ?? [];
                    break;
                }
            }

            if ($rustItem) {
                \Log::info('getQuestions: Found item in Rust API', [
                    'hash' => $actualHash,
                    'item_name' => $rustItem['name'] ?? 'No name',
                    'questions_count' => count($rustQuestions)
                ]);

                // Get attempt data for this user
                $attempt = $this->getAttemptForTaker($dataSession);

                // Add item_hash to questions for green indicator consistency
                foreach ($rustQuestions as &$question) {
                    $question['item_hash'] = $actualHash;
                }

                return response()->json([
                    'questions' => $rustQuestions,
                    'attempt' => $attempt,
                    'using_rust_api' => true
                ]);
            } else {
                \Log::warning('getQuestions: Item not found in Rust API, falling back to database', [
                    'hash' => $actualHash
                ]);
            }
        } else {
            \Log::warning('getQuestions: Rust API failed, falling back to database', [
                'hash' => $actualHash,
                'rust_error' => $examData['error'] ?? 'Unknown error'
            ]);
        }

        // FALLBACK: Try database if Rust API fails
        \Log::info('getQuestions: Attempting database fallback', [
            'hash' => $actualHash,
            'using_without_globalscope' => true
        ]);

        $item = Item::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('hash', $actualHash)
            ->first();

        if (!$item) {
            \Log::error('getQuestions: Item not found in database either', [
                'hash' => $actualHash,
                'original_item_hash' => $item_hash,
                'rust_api_tried' => true,
                'database_tried' => true
            ]);

            return response()->json([
                'error' => 'Item not found in Rust API or database',
                'questions' => [],
                'attempt' => null,
                'rust_api_failed' => true,
                'database_failed' => true
            ], 404);
        }

        \Log::info('Item found', [
            'item_id' => $item->id,
            'item_hash' => $item->hash,
            'item_title' => substr($item->title, 0, 50)
        ]);

        // FIXED: Load questions with all necessary relationships
        // Note: 'type' is an accessor, not a relationship, so it shouldn't be in with()
        $questions = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
            ->where('item_id', $item->id)
            ->with(['answers'])
            ->get();

        \Log::info('Questions query result', [
            'questions_count' => $questions->count(),
            'item_id' => $item->id,
            'item_is_vignette' => $item->is_vignette,
            'first_question' => $questions->first() ? [
                'id' => $questions->first()->id,
                'question_text' => substr(strip_tags($questions->first()->question), 0, 100),
                'type' => $questions->first()->type ? $questions->first()->type->name : 'no type',
                'answers_count' => $questions->first()->answers ? $questions->first()->answers->count() : 0
            ] : null
        ]);

        // DEBUG: Log all questions for vignette items
        if ($item->is_vignette) {
            \Log::info('VIGNETTE DEBUG: All questions for this item', [
                'item_id' => $item->id,
                'item_hash' => $item->hash,
                'total_questions_found' => $questions->count(),
                'questions_list' => $questions->map(function($q) {
                    return [
                        'id' => $q->id,
                        'hash' => $q->hash,
                        'question_preview' => substr(strip_tags($q->question), 0, 100),
                        'item_hash' => $q->item_hash
                    ];
                })->toArray()
            ]);

            // Check if there should be more questions in database
            $allQuestionsCount = Question::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('item_id', $item->id)
                ->count();

            \Log::info('VIGNETTE DEBUG: Total questions in database for this item', [
                'item_id' => $item->id,
                'total_questions_in_db' => $allQuestionsCount,
                'loaded_questions_count' => $questions->count()
            ]);
        }

        $attempt = null;

        \Log::info('Looking for attempt', [
            'has_taker_in_session' => isset($dataSession['taker']),
            'has_delivery_in_session' => isset($dataSession['delivery']),
            'taker_data' => isset($dataSession['taker']) ? (is_object($dataSession['taker']) ? [
                'type' => get_class($dataSession['taker']),
                'id' => property_exists($dataSession['taker'], 'id') ? $dataSession['taker']->id : 'no id',
                'attributes' => method_exists($dataSession['taker'], 'getAttributes') ? $dataSession['taker']->getAttributes() : 'no method'
            ] : $dataSession['taker']) : null,
            'delivery_data' => isset($dataSession['delivery']) ? (is_object($dataSession['delivery']) ? [
                'type' => get_class($dataSession['delivery']),
                'id' => property_exists($dataSession['delivery'], 'id') ? $dataSession['delivery']->id : 'no id',
                'attributes' => method_exists($dataSession['delivery'], 'getAttributes') ? $dataSession['delivery']->getAttributes() : 'no method'
            ] : $dataSession['delivery']) : null
        ]);

        if ($dataSession['taker'] && $dataSession['delivery']) {
            // Handle both Eloquent and stdClass objects - try multiple ID extraction methods
            $deliveryId = null;

            if (is_object($dataSession['delivery'])) {
                // Method 1: Direct property access
                if (property_exists($dataSession['delivery'], 'id')) {
                    $deliveryId = $dataSession['delivery']->id;
                }

                // Method 2: Method access (getId)
                if (!$deliveryId && method_exists($dataSession['delivery'], 'getId')) {
                    $deliveryId = $dataSession['delivery']->getId();
                }

                // Method 3: Attribute array access
                if (!$deliveryId && method_exists($dataSession['delivery'], 'getAttributes')) {
                    $attributes = $dataSession['delivery']->getAttributes();
                    $deliveryId = $attributes['id'] ?? null;
                }

                // Method 4: Array access
                if (!$deliveryId && isset($dataSession['delivery']['id'])) {
                    $deliveryId = $dataSession['delivery']['id'];
                }
            }

            \Log::info('Delivery ID extraction', [
                'delivery_id' => $deliveryId,
                'delivery_object_type' => is_object($dataSession['delivery']) ? get_class($dataSession['delivery']) : 'not object',
                'extraction_methods_tried' => ['property', 'getId()', 'getAttributes()', 'array access']
            ]);

            \Log::info('Processing delivery', ['delivery_id' => $deliveryId]);

            if ($deliveryId) {
                $delivery = Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)
                    ->where('id', $deliveryId)
                    ->first();

                \Log::info('Delivery query result', [
                    'delivery_found' => $delivery ? true : false,
                    'delivery_exam_id' => $delivery ? $delivery->exam_id : null
                ]);

                // Try relationship first, then direct query
                $exam = $delivery ? $delivery->exam : null;
                if (!$exam && $delivery && $delivery->exam_id) {
                    $exam = Exam::withoutGlobalScope(\App\Scopes\ClientScope::class)
                        ->where('id', $delivery->exam_id)
                        ->first();
                }

                \Log::info('Exam resolution', [
                    'exam_found' => $exam ? true : false,
                    'exam_id' => $exam ? $exam->id : null
                ]);

                if ($exam) {
                    // Handle taker ID extraction - try multiple methods like delivery
                    $takerId = null;

                    if (is_object($dataSession['taker'])) {
                        // Method 1: Direct property access
                        if (property_exists($dataSession['taker'], 'id')) {
                            $takerId = $dataSession['taker']->id;
                        }

                        // Method 2: Method access (getId)
                        if (!$takerId && method_exists($dataSession['taker'], 'getId')) {
                            $takerId = $dataSession['taker']->getId();
                        }

                        // Method 3: Attribute array access
                        if (!$takerId && method_exists($dataSession['taker'], 'getAttributes')) {
                            $attributes = $dataSession['taker']->getAttributes();
                            $takerId = $attributes['id'] ?? null;
                        }

                        // Method 4: Array access
                        if (!$takerId && isset($dataSession['taker']['id'])) {
                            $takerId = $dataSession['taker']['id'];
                        }
                    }

                    \Log::info('Taker ID extraction', [
                        'taker_id' => $takerId,
                        'taker_object_type' => is_object($dataSession['taker']) ? get_class($dataSession['taker']) : 'not object',
                        'extraction_methods_tried' => ['property', 'getId()', 'getAttributes()', 'array access']
                    ]);

                    if ($takerId) {
                        $questionsId = $questions->pluck('id')->toArray();

                        \Log::info('Searching for attempt', [
                            'taker_id' => $takerId,
                            'exam_id' => $exam->id,
                            'questions_ids' => $questionsId
                        ]);

                        $attempt = Attempt::query()
                            ->where('attempted_by', $takerId)
                            ->where('exam_id', $exam->id)
                            ->with(['questions' => function ($query) use ($questionsId) {
                                $query->whereIn('question_id', $questionsId)
                                      ->withPivot(['answer_hash', 'answer', 'is_correct', 'score']);
                            }])
                            ->latest()->first();

                        \Log::info('Attempt query result', [
                            'attempt_found' => $attempt ? true : false,
                            'attempt_id' => $attempt ? $attempt->id : null,
                            'attempt_questions_count' => $attempt && $attempt->questions ? $attempt->questions->count() : 0
                        ]);

                        // FALLBACK: If no attempt found by taker_id and exam_id, try using delivery_id
                        if (!$attempt && $delivery) {
                            \Log::info('Attempting fallback search using delivery_id', [
                                'delivery_id' => $delivery->id,
                                'taker_id' => $takerId
                            ]);

                            $attempt = Attempt::query()
                                ->where('delivery_id', $delivery->id)
                                ->where('attempted_by', $takerId)
                                ->with(['questions' => function ($query) use ($questionsId) {
                                    $query->whereIn('question_id', $questionsId)
                                          ->withPivot(['answer_hash', 'answer', 'is_correct', 'score']);
                                }])
                                ->latest()->first();

                            \Log::info('Fallback attempt query result', [
                                'attempt_found' => $attempt ? true : false,
                                'attempt_id' => $attempt ? $attempt->id : null,
                                'attempt_questions_count' => $attempt && $attempt->questions ? $attempt->questions->count() : 0
                            ]);
                        }

                        // FINAL FALLBACK: Try to find any attempt for this taker and exam combo
                        if (!$attempt) {
                            \Log::info('Final fallback: any attempt for taker and exam', [
                                'taker_id' => $takerId,
                                'exam_id' => $exam->id
                            ]);

                            $attempt = Attempt::query()
                                ->where('attempted_by', $takerId)
                                ->where('exam_id', $exam->id)
                                ->with(['questions' => function ($query) use ($questionsId) {
                                    $query->whereIn('question_id', $questionsId)
                                          ->withPivot(['answer_hash', 'answer', 'is_correct', 'score']);
                                }])
                                ->orderBy('created_at', 'desc')
                                ->first();

                            \Log::info('Final fallback attempt query result', [
                                'attempt_found' => $attempt ? true : false,
                                'attempt_id' => $attempt ? $attempt->id : null,
                                'attempt_questions_count' => $attempt && $attempt->questions ? $attempt->questions->count() : 0
                            ]);
                        }
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

        // CRITICAL FIX: Add item hash to attempt questions for frontend matching
        if ($attempt && $attempt->questions) {
            $attempt->questions->each(function ($question) use ($item) {
                // Frontend needs item.hash to match with current item, not question.hash
                $question->item_hash = $item->hash;

                \Log::info('Added item hash to attempt question', [
                    'question_hash' => $question->hash,
                    'item_hash' => $item->hash,
                    'question_id' => $question->id,
                    'item_id' => $item->id
                ]);
            });
        }

        \Log::info('Returning questions response', [
            'questions_count' => $questions->count(),
            'attempt_id' => $attempt ? $attempt->id : null,
            'has_attempt_questions' => $attempt ? ($attempt->questions ?? false) : false,
            'attempt_questions_count' => $attempt ? (isset($attempt->questions) ? $attempt->questions->count() : 0) : 0
        ]);

        return response()->json([
            'questions' => $questions,
            'attempt' => $attempt
        ]);
    }

    #[Post('/exam/answer', name: 'exam.answer')]
    public function answer(Request $request): \Illuminate\Http\JsonResponse
    {
        \Log::info('Answer submission received', [
            'attempt_hash' => $request->attempt_hash,
            'answers_count' => count($request->answers_value),
            'answers_value' => $request->answers_value
        ]);

        try {
            $request->validate([
                'attempt_hash' => ['required', new ExistsByHash(Attempt::class)],
                'answers_value' => 'array',
            ]);

            $answerResults = [];
            $attempt = Attempt::byHash($request->attempt_hash);

            // 🔒 CRITICAL SECURITY: Check if attempt has expired
            if (app(ExamTimerService::class)->isAttemptExpired($attempt)) {
                \Log::warning('Attempt rejected - exam time expired', [
                    'attempt_id' => $attempt->id,
                    'attempt_hash' => $request->attempt_hash,
                    'ended_at' => $attempt->ended_at,
                    'current_time' => Carbon::now()->toDateTimeString()
                ]);

                return response()->json([
                    'error' => 'Exam time expired',
                    'expired' => true
                ], 403);
            }

            if (count($request->answers_value) >= 1) {
                foreach ($request->answers_value as $questionHash => $answerValue) {
                    try {
                        \Log::info('Processing answer', [
                            'question_hash' => $questionHash,
                            'answer_value' => $answerValue
                        ]);

                        $question = Question::byHash($questionHash);

                        if (!$question) {
                            \Log::error('Question not found', ['question_hash' => $questionHash]);
                            continue;
                        }

                        $answer = (null !== $question->type && 'multiple-choice' === $question->type->name) ? Answer::byHash($answerValue) : $answerValue;
                        $score = (null !== $question->type && 'multiple-choice' === $question->type->name && $answer->is_correct_answer) ? $question->score : 0;

                        \Log::info('Saving answer to database', [
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'answer' => is_string($answer) ? $answer : $answer->answer ?? 'N/A',
                            'score' => $score
                        ]);

                        // 🔒 CRITICAL FIX: For MCQ, use actual question score, not just is_correct
                        $actualScore = 0;
                        if (null !== $question->type && 'multiple-choice' === $question->type->name) {
                            $actualScore = $answer->is_correct_answer ? $question->score : 0;
                        }

                        $attemptQuestion = AttemptQuestion::updateOrCreate(
                            ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                            [
                                'answer_id' => (null !== $question->type && 'multiple-choice' === $question->type->name) ? $answer->id : null,
                                'answer_hash' => (null !== $question->type && 'multiple-choice' === $question->type->name) ? Answer::idToHash($answer->id) : null,
                                'answer' => (null !== $question->type && 'multiple-choice' === $question->type->name) ? $answer->answer : $answer,
                                'score' => $actualScore, // Use actual calculated score
                                'is_correct' => null !== $question->type && 'multiple-choice' === $question->type->name && $answer->is_correct_answer,
                            ]
                        );

                        \Log::info('Answer saved successfully', [
                            'attempt_question_id' => $attemptQuestion->id,
                            'question_hash' => $questionHash,
                            'actual_score' => $actualScore,
                            'question_score' => $question->score,
                            'is_correct' => null !== $question->type && 'multiple-choice' === $question->type->name && $answer->is_correct_answer
                        ]);

                    } catch (\Exception $e) {
                        \Log::error('Error processing individual answer', [
                            'question_hash' => $questionHash,
                            'answer_value' => $answerValue,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Continue processing other answers even if one fails
                    }
                }
            }

            $this->calculateScore($attempt);

            \Log::info('Answer submission completed successfully', [
                'attempt_hash' => $request->attempt_hash,
                'total_answers_processed' => count($request->answers_value)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Answer saved successfully.',
                'processed_answers' => count($request->answers_value)
            ]);

        } catch (\Exception $e) {
            \Log::error('Critical error in answer submission', [
                'attempt_hash' => $request->attempt_hash,
                'answers_value' => $request->answers_value,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to save answer: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    private function calculateScore($attempt)
    {
        $this->dispatch(new CalculateScore($attempt));
    }

    /**
     * Helper method to get attempt data for taker session
     */
    private function getAttemptForTaker($dataSession)
    {
        try {
            $taker = $dataSession['taker'] ?? null;
            $delivery = $dataSession['delivery'] ?? null;

            if (!$taker || !$delivery) {
                return null;
            }

            // Use existing attempt logic from the main flow
            $attempt = Attempt::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('delivery_id', $delivery->id)
                ->where('attempted_by', $taker->id)
                ->with(['questions' => function ($query) {
                    $query->withPivot(['answer_hash', 'answer', 'is_correct', 'score']);
                }])
                ->first();

            if ($attempt) {
                \Log::info('getAttemptForTaker: Found existing attempt', [
                    'attempt_id' => $attempt->id,
                    'delivery_id' => $delivery->id,
                    'taker_id' => $taker->id
                ]);
            } else {
                \Log::warning('getAttemptForTaker: No attempt found', [
                    'delivery_id' => $delivery->id,
                    'taker_id' => $taker->id
                ]);
            }

            return $attempt;
        } catch (\Exception $e) {
            \Log::error('getAttemptForTaker: Error getting attempt', [
                'error' => $e->getMessage(),
                'session_data' => array_keys($dataSession)
            ]);
            return null;
        }
    }

    /**
     * Timer synchronization endpoint for frontend
     */
    #[Get('/exam/timer/sync', name: 'exam.timer.sync')]
    public function syncTimer(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'attempt_hash' => ['required', new ExistsByHash(Attempt::class)],
            ]);

            $attempt = Attempt::byHash($request->attempt_hash);
            $remainingSeconds = app(ExamTimerService::class)->calculateRemainingSeconds($attempt->delivery, $attempt);
            $isExpired = app(ExamTimerService::class)->isAttemptExpired($attempt);

            \Log::info('Timer sync request', [
                'attempt_id' => $attempt->id,
                'remaining_seconds' => $remainingSeconds,
                'expired' => $isExpired
            ]);

            return response()->json([
                'remaining_seconds' => $remainingSeconds,
                'expired' => $isExpired,
                'server_time' => Carbon::now()->timestamp
            ]);

        } catch (\Exception $e) {
            \Log::error('Timer sync error', [
                'error' => $e->getMessage(),
                'attempt_hash' => $request->attempt_hash ?? null
            ]);

            return response()->json([
                'error' => 'Sync failed',
                'expired' => true // Safety fallback
            ], 500);
        }
    }
}
