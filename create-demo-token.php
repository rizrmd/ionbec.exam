<?php
// Simple script to create demo token
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Client;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "Creating demo token for client 16...\n";

// Find client 16
$client = Client::find(16);
if (!$client) {
    echo "Client 16 not found\n";
    exit;
}

echo "Client: " . $client->name . "\n";

// Find a group for client 16
$group = Group::where('client_id', 16)->first();
if (!$group) {
    echo "No group found for client 16\n";
    exit;
}

echo "Group: " . $group->name . "\n";

// Find a taker
$taker = Taker::where('client_id', 16)->first();
if (!$taker) {
    echo "No taker found for client 16\n";
    exit;
}

echo "Taker: " . $taker->name . "\n";

// Find existing delivery
$delivery = Delivery::where('client_id', 16)->first();
if (!$delivery) {
    echo "No delivery found for client 16\n";
    exit;
}

echo "Delivery: " . $delivery->name . "\n";

// Update delivery status
$delivery->last_status = 'on_progress';
$delivery->save();

// Generate token
$token = strtoupper(Str::random(5));
DB::table('delivery_taker')->updateOrInsert(
    ['delivery_id' => $delivery->id, 'taker_id' => $taker->id],
    ['token' => $token, 'is_login' => false]
);

echo "\nDemo token created:\n";
echo "Token: $token\n";
echo "Use at: https://demo.medxamion.com\n";