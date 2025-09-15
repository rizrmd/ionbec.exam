<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exams\Question;
use App\Models\Attempts\AttemptQuestion;
use Illuminate\Http\JsonResponse;

class QuestionAttemptController extends Controller
{
    public function __construct()
    {
        $this->middleware('allowed:administrator')->except(['getAttempts']);
    }

    /**
     * Get attempt details for a specific question
     */
    public function getAttempts(string $questionHash): JsonResponse
    {
        try {
            // Find the question by hash using HashableId trait
            // Convert hash to ID first, then find with scope bypass
            $questionId = Question::hashToId($questionHash);
            if (!$questionId) {
                throw new \Exception("Invalid question hash");
            }
            $question = Question::withoutGlobalScopes()->findOrFail($questionId);

            // Get all attempts for this question
            $attempts = AttemptQuestion::withoutGlobalScope(\App\Scopes\ClientScope::class)
                ->where('question_id', $question->id)
                ->orderBy('created_at', 'desc')
                ->get(['attempt_id', 'answer', 'is_correct', 'score', 'created_at'])
                ->map(function ($attempt) {
                    return [
                        'attempt_id' => $attempt->attempt_id,
                        'answer' => strip_tags($attempt->answer),
                        'is_correct' => (bool)$attempt->is_correct,
                        'score' => (float)$attempt->score,
                        'created_at' => $attempt->created_at,
                    ];
                });

            // Calculate statistics
            $total = $attempts->count();
            $correct = $attempts->where('is_correct', true)->count();
            $correctness = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'question_hash' => $questionHash,
                'attempts' => $attempts,
                'statistics' => [
                    'total' => $total,
                    'correct' => $correct,
                    'incorrect' => $total - $correct,
                    'correctness' => $correctness,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attempt details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}