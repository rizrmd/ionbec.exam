<?php
/**
 * Regression: back office (admin / examiner) works on the delivery snapshot.
 *
 * A delivery freezes its exam content (snapshot) when it is scheduled. When the exam is
 * edited afterwards (a question set detached -> "Choose exam to attach: 0 selected", another
 * attached), the examiner must still see and score exactly the frozen items, participants'
 * answers must be recorded against them, and the final score must be computed on that set.
 *
 * Runs inside one transaction that is always rolled back (no data is kept). Requires
 * APP_ENV=testing and a disposable database (staging copy or test DB), never production.
 *
 *   APP_ENV=testing USE_RUST_SCORING=false php tests/Regression/DeliverySnapshotBackOfficeScoring.php
 */
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! app()->environment('testing')) {
    throw new RuntimeException('Isolated testing database required.');
}

use App\Http\Controllers\BackOffice\DeliveryController;
use App\Http\Controllers\BackOffice\QuestionSetController;
use App\Http\Controllers\BackOffice\ScoringController;
use App\Jobs\CalculateScore;
use App\Models\Attempts\Attempt;
use App\Models\Attempts\AttemptQuestion;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Services\ExamSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// PHP scoring path (the Rust service is not available here); events are only logged.
putenv('USE_RUST_SCORING=false');
$_ENV['USE_RUST_SCORING'] = $_SERVER['USE_RUST_SCORING'] = 'false';
config(['broadcasting.default' => 'log']);

$db = DB::connection();
$db->beginTransaction();
$checks = 0;
$assert = function (bool $condition, string $message) use (&$checks) {
    if (! $condition) {
        throw new RuntimeException('FAIL: '.$message);
    }
    $checks++;
    echo "  ok  {$message}\n";
};
$props = function ($inertiaResponse): array {
    $property = new ReflectionProperty($inertiaResponse, 'props');
    $property->setAccessible(true);

    return $property->getValue($inertiaResponse);
};
$private = function (object $object, string $method, ...$args) {
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke($object, ...$args);
};

try {
    $now = now()->toDateTimeString();
    $client = 4;
    $examId = $db->selectOne("INSERT INTO exams(code,name,client_id,is_mcq,created_at,updated_at) VALUES('QA_SNAPSHOT_SCORE','QA snapshot scoring',?,true,?,?) RETURNING id", [$client, $now, $now])->id;
    $groupId = $db->selectOne("INSERT INTO groups(name,client_id,created_at,updated_at) VALUES('QA snapshot scoring group',?,?,?) RETURNING id", [$client, $now, $now])->id;

    $order = 0;
    $makeItem = function (string $title, string $type, bool $attach = true) use ($db, $client, $now, $examId, &$order) {
        $itemId = $db->selectOne('INSERT INTO items(title,type,is_random,client_id,created_at,updated_at) VALUES(?, ?, false, ?, ?, ?) RETURNING id', [$title, $type, $client, $now, $now])->id;
        if ($attach) {
            $db->insert('INSERT INTO exam_item(exam_id,item_id,"order") VALUES(?,?,?)', [$examId, $itemId, $order++]);
        }
        $questionId = $db->selectOne('INSERT INTO questions(item_id,type,question,score,"order",client_id,created_at,updated_at) VALUES(?, ?, ?, 100, 1, ?, ?, ?) RETURNING id', [$itemId, $type, "<p>{$title} question text</p>", $client, $now, $now])->id;
        $answerIds = [];
        if ($type === 'multiple-choice') {
            foreach (['A', 'B', 'C'] as $i => $label) {
                $answerIds[] = $db->selectOne('INSERT INTO answers(question_id,answer,is_correct_answer,created_at,updated_at) VALUES(?,?,?,?,?) RETURNING id', [$questionId, "{$title} option {$label}", $i === 1, $now, $now])->id;
            }
        }

        return [$itemId, $questionId, $answerIds];
    };

    [$itemA, $questionA, $answersA] = $makeItem('QA score item A (detached later)', 'multiple-choice');
    [$itemB, $questionB, $answersB] = $makeItem('QA score item B', 'multiple-choice');
    [$itemC, $questionC] = $makeItem('QA score item C (essay)', 'essay');

    $deliveryId = $db->selectOne("INSERT INTO deliveries(exam_id,group_id,name,display_name,scheduled_at,duration,automatic_start,is_anytime,client_id,created_at,updated_at) VALUES(?,?,'QA snapshot scoring delivery','QA snapshot scoring delivery',?,60,false,true,?,?,?) RETURNING id", [$examId, $groupId, $now, $client, $now, $now])->id;
    $delivery = Delivery::withoutGlobalScopes()->findOrFail($deliveryId);
    app(ExamSnapshotService::class)->createSnapshot($delivery);

    // Exam edited after scheduling: A detached (its question set page shows "0 selected"), D attached.
    $db->delete('DELETE FROM exam_item WHERE exam_id = ? AND item_id = ?', [$examId, $itemA]);
    [$itemD, $questionD] = $makeItem('QA score item D (attached after scheduling)', 'multiple-choice');

    $delivery = Delivery::withoutGlobalScopes()->findOrFail($deliveryId);

    echo "Admin notes:\n";
    $info = $delivery->snapshot_info;
    $assert($info['frozen'] === true && $info['frozen_at'] !== null, 'delivery reports that its exam content is frozen (snapshot) with a timestamp');
    $assert($info['snapshot_items'] === 3 && $info['live_exam_items'] === 3 && $info['differs_from_exam'] === true, 'delivery reports that the exam now differs from the frozen content (same size, different items)');
    $assert(! array_key_exists('snapshot_info', $delivery->toArray()), 'snapshot_info is not appended by default (no extra queries on list pages)');
    $assert((int) $delivery->questions_count === 3, 'delivery question count follows the snapshot (A, B, C), not the edited exam');

    $itemModelA = Item::withoutGlobalScopes()->findOrFail($itemA);
    $detail = $props(app(QuestionSetController::class)->detail(Request::create('/'), $itemModelA));
    $snapshotDeliveries = collect($detail['snapshotDeliveries']);
    $assert($detail['item']->exams->isEmpty(), 'detached item A has no attached exam (the selector count of 0 is correct)');
    $row = $snapshotDeliveries->firstWhere('name', 'QA snapshot scoring delivery');
    $assert($row !== null && $row['attached_to_exam'] === false, 'question set A lists the delivery that still uses it, flagged as no longer attached');
    $itemModelD = Item::withoutGlobalScopes()->findOrFail($itemD);
    $detailD = $props(app(QuestionSetController::class)->detail(Request::create('/'), $itemModelD));
    $assert(collect($detailD['snapshotDeliveries'])->firstWhere('name', 'QA snapshot scoring delivery') === null, 'item D attached after scheduling is not listed for that delivery');

    $base = $private(app(DeliveryController::class), 'getBaseDataDetail', $delivery, true);
    $assert($base['questionCount'] === 3 && $base['questionId'] === [$questionA, $questionB, $questionC], 'delivery detail pages list the snapshot questions in snapshot order');
    $assert(($base['delivery']->toArray()['snapshot_info']['frozen'] ?? null) === true, 'delivery detail pages receive the frozen-content note');

    echo "Participant answers recorded:\n";
    $takerId = $db->selectOne('SELECT id FROM takers ORDER BY id LIMIT 1')->id;
    $attemptId = $db->selectOne("INSERT INTO attempts(attempted_by,exam_id,delivery_id,ip_address,started_at,client_id,created_at,updated_at) VALUES(?,?,?,'127.0.0.1',?,?,?,?) RETURNING id", [$takerId, $examId, $deliveryId, $now, $client, $now, $now])->id;
    $answerHashA = App\Models\Exams\Answer::idToHash($answersA[1]);
    $answerHashB = App\Models\Exams\Answer::idToHash($answersB[0]);
    // A: correct option, B: wrong option, C: essay text (scored manually below)
    $db->insert('INSERT INTO attempt_question(attempt_id,question_id,answer_id,answer_hash,is_correct,score,created_at,updated_at) VALUES(?,?,?,?,true,100,?,?)', [$attemptId, $questionA, $answersA[1], $answerHashA, $now, $now]);
    $db->insert('INSERT INTO attempt_question(attempt_id,question_id,answer_id,answer_hash,is_correct,score,created_at,updated_at) VALUES(?,?,?,?,false,0,?,?)', [$attemptId, $questionB, $answersB[0], $answerHashB, $now, $now]);
    $db->insert("INSERT INTO attempt_question(attempt_id,question_id,answer,created_at,updated_at) VALUES(?,?,'QA essay answer',?,?)", [$attemptId, $questionC, $now, $now]);
    $attempt = Attempt::withoutGlobalScopes()->findOrFail($attemptId);

    $scoring = $private(app(ScoringController::class), 'loadScoringData', $delivery, $attempt);
    $items = collect($scoring['items']);
    $assert($items->pluck('id')->all() === [$itemA, $itemB, $itemC], 'examiner sees the snapshot items (incl. detached A, without D) in snapshot order');
    $itemJson = json_decode(json_encode($items->values()), true);
    $assert(str_contains($itemJson[0]['questions'][0]['question'], 'QA score item A') && count($itemJson[0]['questions'][0]['answers']) === 3, 'examiner sees question text and all options of detached item A');
    $questionHashes = collect($itemJson)->flatMap(fn ($item) => collect($item['questions'])->pluck('hash'))->all();
    $attemptJson = json_decode(json_encode($scoring['attemptQuests']), true);
    $answeredHashes = collect($attemptJson)->pluck('question.hash')->all();
    $assert(count($answeredHashes) === 3 && array_diff($answeredHashes, $questionHashes) === [], 'every recorded answer matches a shown question by hash (what DetailScoring.vue joins on)');
    $selectedA = collect($attemptJson)->firstWhere('question.hash', Question::idToHash($questionA));
    $assert(($selectedA['answer_hash'] ?? null) === $itemJson[0]['questions'][0]['answers'][1]['hash'], 'the option chosen on item A is the one highlighted for the examiner');

    $scoringDetail = app(DeliveryController::class)->scoringShow(Request::create('/'), $delivery, $attempt);
    if ($scoringDetail instanceof Inertia\Response) {
        $assert(collect($props($scoringDetail)['items'])->pluck('id')->all() === [$itemA, $itemB, $itemC], 'delivery scoring detail page shows the same snapshot items');
    }

    echo "Examiner score submission:\n";
    $hashC = Question::idToHash($questionC);
    $hashD = Question::idToHash($questionD);
    $submit = Request::create('/back-office/scoring/submit-score', 'POST', [
        'attempt_hash' => $attempt->hash,
        'corrects' => [$hashC => true, $hashD => true, 'not-a-question' => true],
        'scores' => [$hashC => 80, $hashD => 100, 'not-a-question' => 100],
    ]);
    app()->instance('request', $submit);
    $response = app(ScoringController::class)->submitScore($submit);
    $body = $response->getData(true);
    $assert($response->getStatusCode() === 200 && $body['success'] === true, 'score submission succeeds even with a question outside the delivery');
    $assert(collect($body['skipped'])->sort()->values()->all() === collect([$hashD, 'not-a-question'])->sort()->values()->all(), 'questions outside the snapshot (D) and invalid hashes are skipped and reported');
    $rowC = AttemptQuestion::query()->where('attempt_id', $attemptId)->where('question_id', $questionC)->first();
    $assert($rowC && (float) $rowC->score === 80.0 && (bool) $rowC->is_correct === true && $rowC->answer === 'QA essay answer', 'essay score is recorded on the participant answer row (answer text kept)');
    $assert(! AttemptQuestion::query()->where('attempt_id', $attemptId)->where('question_id', $questionD)->exists(), 'no score row is created for item D that the participant never received');
    $assert(AttemptQuestion::query()->where('attempt_id', $attemptId)->count() === 3, 'still exactly one row per answered snapshot question');

    $private(app(DeliveryController::class), 'checkAndFinishScoring', $attempt);
    $assert((bool) $attempt->fresh()->finish_scoring === true, 'scoring is finished once all snapshot questions are scored (D does not block it)');

    echo "Final score:\n";
    (new CalculateScore($attempt->fresh()))->handle();
    $attempt->refresh();
    $assert(abs((float) $attempt->score - 60.0) < 0.001, 'score = (100 + 0 + 80) / 3 = 60 on the snapshot items (got '.$attempt->score.')');
    $assert((int) $attempt->progress === 100, 'progress = 3 answered / 3 snapshot questions = 100%');

    $db->rollBack();
    echo "PASS {$checks} checks; transaction rolled back\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, $e->getMessage()."\n".$e->getTraceAsString()."\n");
    exit(1);
}
