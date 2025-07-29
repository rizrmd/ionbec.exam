<?php

namespace App\Services;

use App\Models\Exams\Question;
use App\Models\Attempts\Attempt;
use App\Events\LiveInterviewEvent;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Cache;

class LiveInterview
{
    protected ?string $cacheKey = null;

    public function __construct(
        protected Delivery $delivery,
    ) {
        $this->cacheKey = "last_live-interview-{$this->delivery->hash}";
    }

    public function getLastLiveQuestion(): ?Question
    {
        if (! Cache::has($this->cacheKey)) {
            return null;
        }

        $cached = Cache::get($this->cacheKey);

        return $cached['question'];
    }

    public function getLastLiveAttempt(): ?Attempt
    {
        if (! Cache::has($this->cacheKey)) {
            return null;
        }

        $cached = Cache::get($this->cacheKey);

        return $cached['attempt'];
    }

    public function getLastLive(): ?array
    {
        if (! Cache::has($this->cacheKey)) {
            return null;
        }

        return Cache::get($this->cacheKey);
    }

    public function getFirstQuestion(Attempt $attempt): Question
    {
        return $attempt->questions()->first();
    }

    public function clear(): void
    {
        Cache::forget($this->cacheKey);
    }

    public function show(Attempt $attempt, Question $question): void
    {
        $attempt->setRelation('delivery', $this->delivery);
        $attempt->load(['taker']);

        Cache::put($this->cacheKey, [
            'attempt' => $attempt->toArray(),
            'question' => $question->toArray(),
        ], now()->addMinutes(120));

        event(new LiveInterviewEvent($attempt, $question));
    }
}
