<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Get first taker from Test Server group
$taker = Taker::whereHas('groups', function($query) {
    $query->where('client_id', 16);
})->first();

if (!$taker) {
    echo "No taker found for client 16\n";
    exit;
}

// Get the Demo Test delivery
$delivery = Delivery::where('group_id', 41)->first();

if (!$delivery) {
    echo "No delivery found\n";
    exit;
}

// Generate and save token directly
$token = strtoupper(Str::random(5));
DB::table('delivery_taker')->updateOrInsert(
    ['delivery_id' => $delivery->id, 'taker_id' => $taker->id],
    ['token' => $token, 'is_login' => false]
);

echo "Exam token for testing:\n";
echo "Token: $token\n";
echo "Taker: {$taker->name}\n";
echo "Delivery: {$delivery->name}\n";
echo "Use this token at: https://medxamion.com\n";