<?php

namespace App\Jobs;

use App\Models\Exams\Item;
use App\Events\ScoringEvent;
use Illuminate\Bus\Queueable;
use App\Models\Exams\Question;
use App\Models\Attempts\Attempt;
use Illuminate\Queue\SerializesModels;
use App\Models\Attempts\AttemptQuestion;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CalculateScore implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected Attempt $attempt,
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if Rust service is available
        $useRustService = env('USE_RUST_SCORING', true);
        
        if ($useRustService) {
            try {
                // Use Rust service for scoring
                $rustService = app(\App\Services\RustService::class);
                $result = $rustService->calculateScoreForAttempt($this->attempt->id);
                
                if (!isset($result['error'])) {
                    // Successfully calculated using Rust
                    event(new ScoringEvent($this->attempt->fresh()));
                    
                    \Log::info('Score calculated using Rust service', [
                        'attempt_id' => $this->attempt->id,
                        'score' => $result['score'] ?? 0,
                        'processing_time_ms' => $result['processing_time_ms'] ?? 0
                    ]);
                    
                    return;
                }
                
                // Fall back to PHP if Rust fails
                \Log::warning('Rust scoring failed, falling back to PHP', [
                    'attempt_id' => $this->attempt->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            } catch (\Exception $e) {
                // Fall back to PHP if Rust service is unavailable
                \Log::warning('Rust service unavailable, using PHP scoring', [
                    'attempt_id' => $this->attempt->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Original PHP implementation (fallback)
        // why distinct? because somehow, one question can be answered twice (bug?)
        $attemptQuestionsQuery = $this->attempt->attemptQuestions()->distinct('question_id'); // 100

        if (0 == $attemptQuestionsQuery->count()) {
            $this->attempt->score = 0;
            $this->attempt->progress = 0;
            $this->attempt->save();

            event(new ScoringEvent($this->attempt));

            return;
        }

        // Get total questions from snapshot if available, otherwise from current exam
        $delivery = $this->attempt->delivery;
        $totalQuestions = 0;
        $itemIds = [];

        if ($delivery && $delivery->snapshot) {
            // Use snapshot for accurate question count
            $totalQuestions = $delivery->snapshot->total_questions;
            // Get item IDs from snapshot
            $itemIds = collect($delivery->snapshot->exam_structure['items'] ?? [])->pluck('id');
        } else {
            // Fallback to current exam structure (legacy behavior)
            $itemQuery = Item::query()
                ->with('questions')
                ->whereHas(
                    'exams',
                    fn (Builder $builder) => $builder->where('id', $this->attempt->exam_id)
                );
            $itemIds = $itemQuery->pluck('id');
            $totalQuestions = Question::query()->whereIn('item_id', $itemIds)->count();
        }

        // Always query items from database for scoring logic
        $itemQuery = Item::query()
            ->with('questions')
            ->whereHas(
                'exams',
                fn (Builder $builder) => $builder->where('id', $this->attempt->exam_id)
            );
        $items = $itemQuery->cursor();
        $totalScore = 0;
        $totalItems = 0;

        foreach ($items as $item) {
            $questions = $item->questions;
            $is_mcq = false;
            $is_interview = false;

            foreach ($questions as $question) {
                /** @var AttemptQuestion|null $attemptQuestion */
                $attemptQuestion = $question->attempts()->where('attempt_id', $this->attempt->id)->latest()->first();

                if (! $attemptQuestion) {
                    ++$totalItems;
                    continue;
                }

                if ('multiple-choice' == $question?->type?->name) {
                    $is_mcq = true;

                    $score = $attemptQuestion?->pivot?->is_correct ? 100 : 0;
                    $attemptQuestion->score = $score;
                    $attemptQuestion->save();

                    $totalScore += $score;
                } elseif ('essay' == $question?->type?->name) {
                    // manually updated by users; ignore score assignment.
                    $totalScore += $attemptQuestion->pivot->score;
                } elseif ('interview' == $question?->type?->name) {
                    $is_interview = true;

                    // manually updated by users; ignore score assignment.
                    $totalScore += $attemptQuestion->pivot->score;
                }

                // when attemptQuestion is null

                ++$totalItems;
            }

            if (! $is_mcq && $is_interview) {
                // skip
            } elseif (! $is_mcq && $item->is_vignette) { // if not mcq and it's vignette.
                $totalItems -= ($questions->count() - 1);
            }
        }

        $this->attempt->score = $totalScore / $totalItems;

        // Calculate progress and cap at 100% to prevent issues with orphaned questions
        $calculatedProgress = ceil($attemptQuestionsQuery->count() * 100 / $totalQuestions);
        $this->attempt->progress = min($calculatedProgress, 100);

        $this->attempt->save();

        event(new ScoringEvent($this->attempt));
    }
}
