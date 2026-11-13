<?php
/**
 * Regression: GET /exam/questions/{hash} and POST /exam/answer for a delivery with a snapshot.
 *
 * Endpoint-level companion of DeliverySnapshotQuestionLoading.php. Calls the participant
 * controller actions (MainController::getQuestions / ::answer) with an exam session, as the
 * exam page does after index() served snapshot items without questions (rust-service absent).
 * Checks that question text, options and the image come back from the delivery snapshot, that
 * an unknown hash returns a clear JSON 404 / missing session a JSON 401, and that an answer
 * submitted with the returned hashes is stored on the right question and scored.
 *
 * Runs inside one transaction that is always rolled back. Requires APP_ENV=testing and a
 * disposable database (staging copy or test DB), never production.
 *
 *   APP_ENV=testing php tests/Regression/DeliverySnapshotQuestionEndpoint.php
 */
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! app()->environment('testing')) {
    throw new RuntimeException('Isolated testing database required.');
}

use App\Http\Controllers\Exam\MainController;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Item;
use App\Services\ExamSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

config(['session.driver' => 'array', 'broadcasting.default' => 'log', 'app.debug' => false]);

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
$call = function (string $method, ...$args) {
    $controller = app(MainController::class);
    $response = $controller->{$method}(...$args);

    return [$response->getStatusCode(), json_decode($response->getContent(), true)];
};

try {
    $now = now()->toDateTimeString();
    $client = 4;
    $examId = $db->selectOne("INSERT INTO exams(code,name,client_id,is_mcq,created_at,updated_at) VALUES('QA_SNAPSHOT_EP','QA snapshot endpoint',?,true,?,?) RETURNING id", [$client, $now, $now])->id;
    $groupId = $db->selectOne("INSERT INTO groups(name,client_id,created_at,updated_at) VALUES('QA snapshot endpoint group',?,?,?) RETURNING id", [$client, $now, $now])->id;

    $makeItem = function (string $title, int $order) use ($db, $client, $now, $examId) {
        // items.hash NULL: Item never persists its hash, so the old items.hash lookup fails
        $itemId = $db->selectOne("INSERT INTO items(title,type,is_random,client_id,hash,created_at,updated_at) VALUES(?, 'multiple-choice', false, ?, NULL, ?, ?) RETURNING id", [$title, $client, $now, $now])->id;
        $db->insert('INSERT INTO exam_item(exam_id,item_id,"order") VALUES(?,?,?)', [$examId, $itemId, $order]);
        $questionId = $db->selectOne("INSERT INTO questions(item_id,type,question,score,\"order\",client_id,created_at,updated_at) VALUES(?, 'multiple-choice', ?, 100, 1, ?, ?, ?) RETURNING id", [$itemId, "<p>{$title} question text</p>", $client, $now, $now])->id;
        $answerIds = [];
        foreach (['A', 'B', 'C', 'D'] as $i => $label) {
            $answerIds[] = $db->selectOne('INSERT INTO answers(question_id,answer,is_correct_answer,created_at,updated_at) VALUES(?,?,?,?,?) RETURNING id', [$questionId, "{$title} option {$label}", $i === 2, $now, $now])->id;
        }

        return [$itemId, $questionId, $answerIds];
    };

    [$item1, $question1, $answers1] = $makeItem('QA endpoint item 1', 0);
    [$item2, $question2, $answers2] = $makeItem('QA endpoint item 2', 1);

    $attachmentId = (string) Illuminate\Support\Str::uuid();
    $db->insert("INSERT INTO attachments(id,uploaded_by,title,path,mime,client_id,created_at,updated_at) VALUES(?, (SELECT id FROM users ORDER BY id LIMIT 1), 'qa image', 'attachments/qa-endpoint.png', 'png', ?, ?, ?)", [$attachmentId, $client, $now, $now]);
    $db->insert("INSERT INTO attachables(attachment_id,attachable_type,attachable_id) VALUES(?, 'App\\Models\\Exams\\Item', ?)", [$attachmentId, $item1]);

    $deliveryId = $db->selectOne("INSERT INTO deliveries(exam_id,group_id,name,display_name,scheduled_at,duration,automatic_start,is_anytime,client_id,created_at,updated_at) VALUES(?,?,'QA snapshot endpoint','QA snapshot endpoint',?,60,false,true,?,?,?) RETURNING id", [$examId, $groupId, $now, $client, $now, $now])->id;
    $delivery = Delivery::withoutGlobalScopes()->findOrFail($deliveryId);
    app(ExamSnapshotService::class)->createSnapshot($delivery);
    $delivery = Delivery::withoutGlobalScopes()->findOrFail($deliveryId);
    $snapshotItems = collect($delivery->snapshot->exam_structure['items'])->keyBy('id');
    $snapHash1 = $snapshotItems[$item1]['hash'];
    $snapHash2 = $snapshotItems[$item2]['hash'];

    $takerId = (int) $db->selectOne('SELECT id FROM takers ORDER BY id LIMIT 1')->id;
    $attemptId = $db->selectOne("INSERT INTO attempts(attempted_by,exam_id,delivery_id,ip_address,started_at,client_id,created_at,updated_at) VALUES(?,?,?,'127.0.0.1',?,?,?,?) RETURNING id", [$takerId, $examId, $deliveryId, $now, $client, $now, $now])->id;
    $attempt = App\Models\Attempts\Attempt::withoutGlobalScopes()->findOrFail($attemptId);

    echo "Without exam session:\n";
    Session::start();
    Session::forget('exam');
    [$status, $body] = $call('getQuestions', $snapHash1);
    $assert($status === 401 && ($body['error'] ?? null) === 'exam_session_missing', 'missing session returns JSON 401 exam_session_missing');

    Session::put('exam', ['delivery' => ['id' => $deliveryId], 'taker' => ['id' => $takerId]]);

    echo "GET /exam/questions/{snapshot hash}:\n";
    $assert(! Item::withoutGlobalScopes()->where('hash', $snapHash1)->exists(), 'snapshot hash is not in items.hash (old lookup -> 404)');
    [$status, $body] = $call('getQuestions', $snapHash1);
    $assert($status === 200, 'item 1 returns HTTP 200');
    $assert(($body['source'] ?? null) === 'delivery_snapshot', 'served from the delivery snapshot');
    $assert(($body['item_id'] ?? null) === $item1, 'resolved item id is item 1');
    $assert(count($body['questions'] ?? []) === 1 && str_contains($body['questions'][0]['question'], 'QA endpoint item 1 question text'), 'question text is returned');
    $options = $body['questions'][0]['answers'] ?? [];
    $assert(count($options) === 4 && str_contains($options[2]['answer'], 'option C'), 'all four options are returned');
    $assert(collect($options)->every(fn ($o) => ! array_key_exists('is_correct_answer', $o)), 'answer key is not exposed');
    $image = $body['attachments'][0] ?? [];
    $assert(count($body['attachments'] ?? []) === 1 && ($image['path'] ?? null) === 'attachments/qa-endpoint.png' && str_contains($image['url'] ?? '', '/attachment/stream/'.$attachmentId), 'image is returned with path and stream URL');
    $assert(($body['archived'] ?? null) === false, 'item 1 is answerable');
    $questionHash1 = $body['questions'][0]['hash'];
    $correctHash1 = $options[2]['hash'];

    echo "GET with stale hash + item_id (page sends the stable id):\n";
    request()->query->set('item_id', (string) $item2);
    [$status, $body2] = $call('getQuestions', 'stale-hash-from-old-page');
    request()->query->remove('item_id');
    $assert($status === 200 && ($body2['item_id'] ?? null) === $item2, 'item 2 resolved by item_id');
    $assert($snapHash2 !== 'stale-hash-from-old-page', 'snapshot hash of item 2 differs from the sent hash');

    echo "GET with unknown hash:\n";
    [$status, $body] = $call('getQuestions', 'definitely-not-an-item-hash');
    $assert($status === 404, 'unknown hash returns HTTP 404');
    $assert(($body['error'] ?? null) === 'question_not_found' && ($body['retryable'] ?? null) === true && ! empty($body['message']), 'JSON error code, message and retryable flag');
    $assert(! array_key_exists('debug_info', $body), 'no debug info when app.debug is off');

    echo "POST /exam/answer with the hashes the page received:\n";
    $request = Request::create('/exam/answer', 'POST', [
        'attempt_hash' => $attempt->hash,
        'answers_value' => [
            $questionHash1 => $correctHash1,                          // correct
            $body2['questions'][0]['hash'] => $body2['questions'][0]['answers'][0]['hash'], // wrong
        ],
    ]);
    app()->instance('request', $request);
    [$status, $body] = $call('answer', $request);
    $assert($status === 200 && ($body['success'] ?? false) === true, 'answer request succeeds');
    $row1 = $db->selectOne('SELECT answer_id, is_correct, score FROM attempt_question WHERE attempt_id = ? AND question_id = ?', [$attemptId, $question1]);
    $row2 = $db->selectOne('SELECT answer_id, is_correct, score FROM attempt_question WHERE attempt_id = ? AND question_id = ?', [$attemptId, $question2]);
    $assert($row1 && (int) $row1->answer_id === $answers1[2] && $row1->is_correct && (float) $row1->score === 100.0, 'item 1 answer stored on question 1 as correct');
    $assert($row2 && (int) $row2->answer_id === $answers2[0] && ! $row2->is_correct, 'item 2 answer stored on question 2 as wrong');
    $attempt->refresh();
    $assert((float) $attempt->score === 50.0, 'attempt score recorded (1 of 2 correct = 50)');

    echo "GET after answering returns the saved answer:\n";
    [$status, $body] = $call('getQuestions', $snapHash1);
    $saved = collect($body['attempt']['questions'] ?? [])->first(fn ($q) => (int) ($q['pivot']['question_id'] ?? 0) === $question1);
    $assert($status === 200 && $saved && ($saved['pivot']['answer_hash'] ?? null) === $correctHash1, 'attempt contains the saved answer for item 1');
    $assert(($saved['item_hash'] ?? null) === $snapHash1, 'saved answer is tagged with the snapshot item hash the page uses');

    $db->rollBack();
    echo "PASS {$checks} checks; transaction rolled back\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, $e->getMessage()."\n".$e->getTraceAsString()."\n");
    exit(1);
}
