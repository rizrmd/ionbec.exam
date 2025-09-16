<?php
// Create exam token with questions
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Client;
use App\Models\Takers\Taker;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "Creating exam token with questions...\n\n";

// Find client 16
$client = Client::find(16);
echo "Client: " . $client->name . "\n";

// Find exam with questions
$exam = Exam::with(['items.questions'])->first();
echo "Exam: " . $exam->name . "\n";
echo "Total questions: " . $exam->items->sum(function($item) { 
    return $item->questions->count(); 
}) . "\n\n";

// Create new delivery
$delivery = Delivery::create([
    "name" => "Demo Exam with Questions - " . date("Ymd His"),
    "group_id" => 41,
    "exam_id" => $exam->id,
    "client_id" => 16,
    "scheduled_at" => now(),
    "duration" => 60,
    "automatic_start" => true,
    "last_status" => "on_progress"
]);

echo "Created delivery: " . $delivery->name . "\n";
echo "Delivery ID: " . $delivery->id . "\n\n";

// Generate token
$token = strtoupper(Str::random(5));
DB::table("delivery_taker")->insert([
    "delivery_id" => $delivery->id,
    "taker_id" => 314,
    "token" => $token,
    "is_login" => 0
]);

echo "✅ New exam token created!\n\n";
echo "Token: $token\n";
echo "Questions: " . $exam->items->sum(function($item) { 
    return $item->questions->count(); 
}) . " questions\n";
echo "Use at: https://demo.medxamion.com\n\n";
echo "Note: The back button issue exists because client domains don't have login routes.\n";
echo "Use the Logout button instead of back button to exit the exam.\n";