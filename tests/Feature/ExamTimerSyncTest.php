<?php

namespace Tests\Feature;

use App\Models\Deliveries\Delivery;
use App\Models\Attempts\Attempt;
use App\Models\Takers\Taker;
use App\Models\Exams\Exam;
use App\Services\ExamTimerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTimerSyncTest extends TestCase
{
    use RefreshDatabase;

    private ExamTimerService $timerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timerService = app(ExamTimerService::class);
    }

    /** @test */
    public function it_calculates_remaining_time_for_automatic_start_delivery()
    {
        $delivery = Delivery::factory()->create([
            'automatic_start' => true,
            'scheduled_at' => Carbon::now()->subMinutes(30),
            'duration' => 60 // 60 minutes total
        ]);

        $remainingSeconds = $this->timerService->calculateRemainingSeconds($delivery);

        // Should be 30 minutes remaining (60 - 30 elapsed)
        $this->assertEquals(30 * 60, $remainingSeconds);
    }

    /** @test */
    public function it_calculates_remaining_time_for_manual_start_with_attempt()
    {
        $delivery = Delivery::factory()->create([
            'automatic_start' => false,
            'duration' => 60
        ]);

        $attempt = Attempt::factory()->create([
            'delivery_id' => $delivery->id,
            'started_at' => Carbon::now()->subMinutes(20)
        ]);

        $remainingSeconds = $this->timerService->calculateRemainingSeconds($delivery, $attempt);

        // Should be 40 minutes remaining (60 - 20 elapsed)
        $this->assertEquals(40 * 60, $remainingSeconds);
    }

    /** @test */
    public function it_includes_extra_minute_in_calculation()
    {
        $delivery = Delivery::factory()->create([
            'automatic_start' => true,
            'scheduled_at' => Carbon::now()->subMinutes(30),
            'duration' => 60
        ]);

        $attempt = Attempt::factory()->create([
            'delivery_id' => $delivery->id,
            'extra_minute' => 15 // 15 extra minutes
        ]);

        $remainingSeconds = $this->timerService->calculateRemainingSeconds($delivery, $attempt);

        // Should be 45 minutes remaining (60 + 15 - 30 elapsed)
        $this->assertEquals(45 * 60, $remainingSeconds);
    }

    /** @test */
    public function it_returns_zero_when_delivery_expired()
    {
        $delivery = Delivery::factory()->create([
            'automatic_start' => true,
            'scheduled_at' => Carbon::now()->subHours(2),
            'duration' => 30 // 30 minutes, already expired
        ]);

        $remainingSeconds = $this->timerService->calculateRemainingSeconds($delivery);

        $this->assertEquals(0, $remainingSeconds);
    }

    /** @test */
    public function it_correctly_identifies_expired_attempts()
    {
        $delivery = Delivery::factory()->create([
            'automatic_start' => true,
            'scheduled_at' => Carbon::now()->subHours(2),
            'duration' => 30
        ]);

        $attempt = Attempt::factory()->create([
            'delivery_id' => $delivery->id
        ]);

        $this->assertTrue($this->timerService->isAttemptExpired($attempt));
    }

    /** @test */
    public function it_prevents_answer_submission_after_expiry()
    {
        $delivery = Delivery::factory()->create([
            'automatic_start' => true,
            'scheduled_at' => Carbon::now()->subHours(2),
            'duration' => 30
        ]);

        $attempt = Attempt::factory()->create([
            'delivery_id' => $delivery->id
        ]);

        // Try to submit answer after expiry
        $response = $this->postJson('/exam/answer', [
            'attempt_hash' => $attempt->hash,
            'answers_value' => ['some_question_hash' => 'some_answer']
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'error' => 'Exam time expired',
                     'expired' => true
                 ]);
    }

    /** @test */
    public function timer_sync_endpoint_returns_correct_data()
    {
        $delivery = Delivery::factory()->create([
            'automatic_start' => true,
            'scheduled_at' => Carbon::now()->subMinutes(30),
            'duration' => 60
        ]);

        $attempt = Attempt::factory()->create([
            'delivery_id' => $delivery->id
        ]);

        $response = $this->getJson('/exam/timer/sync?attempt_hash=' . $attempt->hash);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'remaining_seconds',
                     'expired',
                     'server_time'
                 ]);

        $this->assertFalse($response->json('expired'));
        $this->assertGreaterThan(0, $response->json('remaining_seconds'));
    }
}