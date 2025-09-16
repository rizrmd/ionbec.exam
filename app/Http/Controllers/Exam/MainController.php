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
        $delivery = Delivery::query()->where('id', $dataSession['delivery']->id)->first();

        // Check also ScheduleController->login
        if (! $dataSession['admin'] && $delivery->automatic_start) {
            if (strtotime($delivery->scheduled_at) >= strtotime('now')) {
                return redirect()->route('exam.waiting-room');
            }
        }

        // query taker and exam only when not in waiting-room.
        $taker = $dataSession['taker'] ? Taker::query()->where('id', $dataSession['taker']->id)->first() : null;
        
        $exam = $delivery->exam;
        if (!$exam) {
            return redirect()->route('exam.finished')->with('error', 'Exam not found');
        }

        // Try Rust service for fast data loading, fallback to PHP
        $rustService = new RustService();
        $examData = $rustService->loadExamData(
            $exam->id, 
            $delivery->id, 
            $taker ? $taker->id : null
        );
        
        if ($examData['success'] ?? false) {
            // Use Rust-processed data
            $items = collect($examData['items']);
        } else {
            // Fallback to PHP (your previous lazy loading approach)
            $items = $exam->items()->with(['attachments'])->orderByPivot('order')->get();
            foreach ($items as $item) {
                $item->questions_count = $item->questions()->count();
                $item->questions = collect();
            }
        }

        // Calculate remaining time in seconds (timezone agnostic)
        $remainingSeconds = 0;
        if ($delivery->automatic_start) {
            $endTime = $delivery->scheduled_at->copy()->addMinutes($delivery->duration);
            $remainingSeconds = max(0, $endTime->diffInSeconds(Carbon::now()));
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
            
            // For non-automatic start, calculate remaining time from attempt start
            if (!$delivery->automatic_start && $remainingSeconds == 0) {
                $endTime = $attempt->started_at->copy()->addMinutes($delivery->duration);
                $remainingSeconds = max(0, $endTime->diffInSeconds(Carbon::now()));
                $data['remainingSeconds'] = $remainingSeconds;
            }
        }

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
            $delivery = $dataSession['delivery'];
            $exam = $delivery->exam;
            
            if ($exam) {
                $attempt = Attempt::query()
                    ->where('attempted_by', $dataSession['taker']->id)
                    ->where('exam_id', $exam->id)
                    ->with('questions', function ($query) use ($questionsId) {
                        $query->whereIn('question_id', $questionsId);
                    })
                    ->latest()->first();
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
