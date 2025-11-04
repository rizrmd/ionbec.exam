<?php

namespace App\Services;

use App\Models\Exams\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Question Duplicate Prevention Service
 *
 * Provides backend validation and prevention of question duplication
 */
class QuestionDuplicatePreventionService
{
    /**
     * Check if a question would be a duplicate
     *
     * @param int $itemId
     * @param string $questionContent
     * @param int|null $excludeQuestionId
     * @return bool
     */
    public function isDuplicate(int $itemId, string $questionContent, ?int $excludeQuestionId = null): bool
    {
        $query = Question::where('item_id', $itemId)
            ->where('question', $questionContent);

        if ($excludeQuestionId) {
            $query->where('id', '!=', $excludeQuestionId);
        }

        return $query->exists();
    }

    /**
     * Log duplicate prevention attempts
     *
     * @param int $itemId
     * @param string $questionContent
     * @param string $reason
     * @return void
     */
    public function logDuplicateAttempt(int $itemId, string $questionContent, string $reason): void
    {
        try {
            DB::table('question_duplicate_prevention_logs')->insert([
                'item_id' => $itemId,
                'attempted_question' => $questionContent,
                'attempted_by' => auth()->user()->name ?? 'Unknown',
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'prevented_reason' => $reason,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log duplicate prevention attempt', [
                'error' => $e->getMessage(),
                'item_id' => $itemId,
            ]);
        }

        Log::warning('Question duplicate prevented', [
            'item_id' => $itemId,
            'question_preview' => substr($questionContent, 0, 100),
            'reason' => $reason,
            'user' => auth()->user()->name ?? 'Unknown',
            'ip' => Request::ip(),
        ]);
    }

    /**
     * Validate question before creation/update
     *
     * @param int $itemId
     * @param string $questionContent
     * @param int|null $excludeQuestionId
     * @return array
     */
    public function validateQuestion(int $itemId, string $questionContent, ?int $excludeQuestionId = null): array
    {
        $questionContent = trim($questionContent);

        // Check for empty content
        if (empty($questionContent)) {
            return [
                'valid' => false,
                'error' => 'Question content cannot be empty',
            ];
        }

        // Check for duplicate content
        if ($this->isDuplicate($itemId, $questionContent, $excludeQuestionId)) {
            $reason = $excludeQuestionId
                ? 'Duplicate content detected for update'
                : 'Duplicate content detected for new question';

            $this->logDuplicateAttempt($itemId, $questionContent, $reason);

            return [
                'valid' => false,
                'error' => 'A question with this content already exists for this item',
            ];
        }

        // Check for very similar content (optional strict validation)
        if ($this->isVerySimilar($itemId, $questionContent, $excludeQuestionId)) {
            return [
                'valid' => false,
                'error' => 'A very similar question already exists for this item. Please make your question more distinct.',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Check for very similar content using string similarity
     *
     * @param int $itemId
     * @param string $questionContent
     * @param int|null $excludeQuestionId
     * @param float $threshold
     * @return bool
     */
    private function isVerySimilar(int $itemId, string $questionContent, ?int $excludeQuestionId = null, float $threshold = 0.85): bool
    {
        $existingQuestions = Question::where('item_id', $itemId)
            ->when($excludeQuestionId, fn($q) => $q->where('id', '!=', $excludeQuestionId))
            ->pluck('question');

        foreach ($existingQuestions as $existing) {
            $similarity = $this->calculateSimilarity($questionContent, $existing);
            if ($similarity >= $threshold) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate string similarity percentage
     *
     * @param string $str1
     * @param string $str2
     * @return float
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(strip_tags($str1));
        $str2 = strtolower(strip_tags($str2));

        similar_text($str1, $str2, $percent);
        return $percent / 100;
    }

    /**
     * Get duplicate prevention statistics
     *
     * @return array
     */
    public function getPreventionStats(): array
    {
        try {
            $stats = DB::table('question_duplicate_prevention_logs')
                ->selectRaw('COUNT(*) as total_prevented')
                ->selectRaw('COUNT(DISTINCT item_id) as affected_items')
                ->selectRaw('DATE(created_at) as date')
                ->selectRaw('COUNT(*) as daily_count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get();

            $total = DB::table('question_duplicate_prevention_logs')->count();

            return [
                'total_prevented' => $total,
                'stats' => $stats,
                'recent_attempts' => DB::table('question_duplicate_prevention_logs')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get prevention stats', ['error' => $e->getMessage()]);
            return [
                'total_prevented' => 0,
                'stats' => collect(),
                'recent_attempts' => collect(),
            ];
        }
    }
}