<?php
/**
 * Regression: lazy question loading must not depend on items.hash.
 *
 * Reproduces the exam.ionbec.com incident where a participant saw only "Question #1" and the
 * image: the page received snapshot items without questions, and GET /exam/questions/{hash}
 * looked the item up by the items.hash column, which did not contain the snapshot hash.
 *
 * Runs inside one transaction that is always rolled back (no data is kept). Requires
 * APP_ENV=testing and a disposable database (staging copy or test DB), never production.
 *
 *   APP_ENV=testing php tests/Regression/DeliverySnapshotQuestionLoading.php
 */
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! app()->environment('testing')) {
    throw new RuntimeException('Isolated testing database required.');
}

use App\Models\Deliveries\Delivery;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Services\DeliveryItemSource;
use App\Services\ExamSnapshotService;
use Illuminate\Support\Facades\DB;

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

try {
    $now = now()->toDateTimeString();
    $client = 4;
    $examId = $db->selectOne("INSERT INTO exams(code,name,client_id,is_mcq,created_at,updated_at) VALUES('QA_SNAPSHOT_LOAD','QA snapshot lazy load',?,true,?,?) RETURNING id", [$client, $now, $now])->id;
    $groupId = $db->selectOne("INSERT INTO groups(name,client_id,created_at,updated_at) VALUES('QA snapshot group',?,?,?) RETURNING id", [$client, $now, $now])->id;

    $makeItem = function (string $title, ?string $hash) use ($db, $client, $now, $examId) {
        $itemId = $db->selectOne("INSERT INTO items(title,type,is_random,client_id,hash,created_at,updated_at) VALUES(?, 'multiple-choice', false, ?, ?, ?, ?) RETURNING id", [$title, $client, $hash, $now, $now])->id;
        $db->insert('INSERT INTO exam_item(exam_id,item_id,"order") VALUES(?,?,0)', [$examId, $itemId]);
        $questionId = $db->selectOne("INSERT INTO questions(item_id,type,question,score,\"order\",client_id,created_at,updated_at) VALUES(?, 'multiple-choice', ?, 100, 1, ?, ?, ?) RETURNING id", [$itemId, "<p>{$title} question text</p>", $client, $now, $now])->id;
        $answerIds = [];
        foreach (['A', 'B', 'C'] as $i => $label) {
            $answerIds[] = $db->selectOne('INSERT INTO answers(question_id,answer,is_correct_answer,created_at,updated_at) VALUES(?,?,?,?,?) RETURNING id', [$questionId, "{$title} option {$label}", $i === 1, $now, $now])->id;
        }

        return [$itemId, $questionId, $answerIds];
    };

    // A: new item created in the admin -> items.hash NULL (Item never persists its hash)
    [$itemA, $questionA, $answersA] = $makeItem('QA item A (hash null)', null);
    // B: legacy item whose stored hash is later changed by a sync script
    [$itemB, $questionB] = $makeItem('QA item B (legacy hash)', 'qaLegacyB');
    // C: item deleted after the delivery was scheduled
    [$itemC, $questionC] = $makeItem('QA item C (deleted later)', 'qaLegacyC');

    $attachmentId = (string) Illuminate\Support\Str::uuid();
    $db->insert("INSERT INTO attachments(id,uploaded_by,title,path,mime,client_id,created_at,updated_at) VALUES(?, (SELECT id FROM users ORDER BY id LIMIT 1), 'qa', 'attachments/qa-snapshot.jpg', 'jpg', ?, ?, ?)", [$attachmentId, $client, $now, $now]);
    $db->insert("INSERT INTO attachables(attachment_id,attachable_type,attachable_id) VALUES(?, 'App\\Models\\Exams\\Item', ?)", [$attachmentId, $itemA]);

    $deliveryId = $db->selectOne("INSERT INTO deliveries(exam_id,group_id,name,display_name,scheduled_at,duration,automatic_start,is_anytime,client_id,created_at,updated_at) VALUES(?,?,'QA snapshot delivery','QA snapshot delivery',?,60,false,true,?,?,?) RETURNING id", [$examId, $groupId, $now, $client, $now, $now])->id;
    $delivery = Delivery::withoutGlobalScopes()->findOrFail($deliveryId);
    app(ExamSnapshotService::class)->createSnapshot($delivery);
    $delivery = Delivery::withoutGlobalScopes()->findOrFail($deliveryId);
    $snapshotItems = collect($delivery->snapshot->exam_structure['items'])->keyBy('id');
    $snapHashA = $snapshotItems[$itemA]['hash'];
    $snapHashB = $snapshotItems[$itemB]['hash'];
    $snapHashC = $snapshotItems[$itemC]['hash'];

    // Drift after scheduling, as seen in production
    $db->update('UPDATE items SET hash = ? WHERE id = ?', ['qaLegacyB-resynced', $itemB]);
    $db->delete('DELETE FROM exam_item WHERE exam_id = ? AND item_id = ?', [$examId, $itemA]);   // admin shows "0 selected"
    $db->delete('DELETE FROM items WHERE id = ?', [$itemC]);                                       // cascades its questions

    echo "Legacy lookup (old getQuestions DB fallback):\n";
    $assert(! Item::withoutGlobalScopes()->where('hash', $snapHashA)->exists(), 'old lookup by items.hash cannot find item A (snapshot hash = app hash, column NULL) -> was HTTP 404 / blank question');
    $assert(! Item::withoutGlobalScopes()->where('hash', $snapHashB)->exists(), 'old lookup cannot find item B after its stored hash changed');

    echo "DeliveryItemSource:\n";
    $source = app(DeliveryItemSource::class);
    $assert($source->itemIds($delivery)->all() === [$itemA, $itemB, $itemC], 'delivery item ids come from the snapshot, including the detached and deleted item');
    $assert($source->items($delivery)->pluck('id')->all() === [$itemA, $itemB], 'live items of the delivery keep snapshot order and skip the deleted item');
    $assert($source->questionCount($delivery) === 2, 'question count matches the set scoring evaluates (live questions of snapshot items)');

    $assert(($source->findSnapshotItem($delivery, $snapHashA)['id'] ?? null) === $itemA, 'resolves item A by snapshot hash');
    $assert(($source->findSnapshotItem($delivery, 'stale-or-unknown', $itemA)['id'] ?? null) === $itemA, 'resolves item A by stable item id even with an unknown hash');
    $assert(($source->findSnapshotItem($delivery, 'item_'.$itemA)['id'] ?? null) === $itemA, 'resolves the item_{id} fallback hash');
    $assert(($source->findSnapshotItem($delivery, Item::idToHash($itemB))['id'] ?? null) === $itemB, 'resolves item B by the application hash');
    $assert(($source->findSnapshotItem($delivery, $snapHashB)['id'] ?? null) === $itemB, 'resolves item B by its old snapshot hash after the column changed');
    $foreignItemId = (int) $db->selectOne('SELECT id FROM items WHERE id NOT IN (?,?,?) ORDER BY id LIMIT 1', [$itemA, $itemB, $itemC])->id;
    $assert($source->findSnapshotItem($delivery, 'x', $foreignItemId) === null, 'an item id outside the delivery is never served');

    echo "Questions served to the participant:\n";
    $resolvedA = $source->questionsForSnapshotItem($source->findSnapshotItem($delivery, $snapHashA));
    $jsonA = json_decode(json_encode($resolvedA['questions']), true);
    $assert(count($jsonA) === 1 && str_contains($jsonA[0]['question'], 'QA item A'), 'item A returns its question text');
    $assert(count($jsonA[0]['answers']) === 3, 'item A returns all options');
    $assert(! array_key_exists('is_correct_answer', $jsonA[0]['answers'][0]), 'answer keys are not exposed');
    $assert(($jsonA[0]['type']['name'] ?? null) === 'multiple-choice', 'question type serialises as multiple-choice (options are rendered)');
    $assert(Question::byHash($jsonA[0]['hash'])?->id === $questionA, 'question hash is accepted by the answer endpoint (Question::byHash)');
    $answerHash = $jsonA[0]['answers'][1]['hash'];
    $assert(App\Models\Exams\Answer::byHash($answerHash)?->id === $answersA[1], 'option hash is accepted by the answer endpoint (Answer::byHash)');
    $assert(count($resolvedA['attachments']) === 1 && str_contains($resolvedA['attachments'][0]['url'] ?? '', '/attachment/stream/'.$attachmentId), 'item A attachment is returned with its stream URL');
    $assert($resolvedA['archived'] === false, 'item A is answerable');

    $resolvedC = $source->questionsForSnapshotItem($source->findSnapshotItem($delivery, $snapHashC));
    $jsonC = json_decode(json_encode($resolvedC['questions']), true);
    $assert($resolvedC['archived'] === true && count($jsonC) === 1 && ($jsonC[0]['is_archived'] ?? false) === true, 'deleted item C is shown read-only from the snapshot (archived)');
    $assert(! array_key_exists('is_correct_answer', $jsonC[0]['answers'][0] ?? []), 'archived answers do not expose the key');

    echo "Scoring consistency:\n";
    $attemptId = $db->selectOne("INSERT INTO attempts(attempted_by,exam_id,delivery_id,ip_address,started_at,client_id,created_at,updated_at) VALUES((SELECT id FROM takers ORDER BY id LIMIT 1),?,?,'127.0.0.1',?,?,?,?) RETURNING id", [$examId, $deliveryId, $now, $client, $now, $now])->id;
    $db->insert('INSERT INTO attempt_question(attempt_id,question_id,answer_id,answer_hash,is_correct,score,created_at,updated_at) VALUES(?,?,?,?,true,100,?,?)', [$attemptId, $questionA, $answersA[1], $answerHash, $now, $now]);
    $attempt = App\Models\Attempts\Attempt::withoutGlobalScopes()->findOrFail($attemptId);
    $calc = new ReflectionMethod(App\Http\Controllers\Exam\MainController::class, 'calculateScore');
    $calc->setAccessible(true);
    config(['broadcasting.default' => 'log']);
    $calc->invoke(app(App\Http\Controllers\Exam\MainController::class), $attempt);
    $attempt->refresh();
    $assert((float) $attempt->score === 50.0, 'answer on detached item A is scored (1 of 2 live questions correct = 50)');

    $db->rollBack();
    echo "PASS {$checks} checks; transaction rolled back\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, $e->getMessage()."\n".$e->getTraceAsString()."\n");
    exit(1);
}
