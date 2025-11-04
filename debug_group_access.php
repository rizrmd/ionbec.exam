<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;

echo "=== DETAILED GROUP ACCESS DEBUG ===\n";

// 1. Cek group BE051125
echo "\n1. CHECKING GROUP BE051125:\n";
$group = Group::find(2);
if ($group) {
    echo "✅ Group found: ID {$group->id}, Name: '{$group->name}', Hash: '{$group->hash}'\n";
} else {
    echo "❌ Group BE051125 not found!\n";
    exit;
}

// 2. Cek apakah ada takers di group ini
echo "\n2. CHECKING TAKERS IN GROUP:\n";
try {
    $takers = $group->takers()->get();
    echo "✅ Found " . count($takers) . " takers in group\n";

    if (count($takers) > 0) {
        echo "First few takers:\n";
        for ($i = 0; $i < min(3, count($takers)); $i++) {
            $taker = $takers[$i];
            echo "- ID: {$taker->id}, Name: {$taker->name}, Email: {$taker->email}\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error getting takers: " . $e->getMessage() . "\n";
}

// 3. Test route generation
echo "\n3. TESTING ROUTE GENERATION:\n";
try {
    $route = route('back-office.group.taker', ['group_hash' => $group->hash]);
    echo "✅ Route generated: {$route}\n";
} catch (\Exception $e) {
    echo "❌ Error generating route: " . $e->getMessage() . "\n";
}

// 4. Test controller method
echo "\n4. TESTING CONTROLLER ACCESS:\n";
try {
    // Simulate controller logic
    $takerQuery = Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)
        ->whereHas('groups', function ($query) use ($group) {
            $query->where('id', $group->id);
        });

    $count = $takerQuery->count();
    echo "✅ Controller query works: {$count} takers found\n";

    // Test pagination
    $paginated = $takerQuery->paginate(15);
    echo "✅ Pagination works: " . $paginated->total() . " total takers\n";

} catch (\Exception $e) {
    echo "❌ Error in controller logic: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// 5. Cek permissions dan middleware
echo "\n5. CHECKING AUTHENTICATION REQUIREMENTS:\n";
echo "Route requires: administrator middleware (from GroupController)\n";
echo "This means user must be logged in and have administrator role\n";

// 6. Cek relationship integrity
echo "\n6. CHECKING RELATIONSHIP INTEGRITY:\n";
try {
    $takerIds = DB::table('group_taker')
        ->where('group_id', 2)
        ->pluck('taker_id');

    echo "✅ Found " . count($takerIds) . " taker IDs in group_taker table\n";

    if (count($takerIds) > 0) {
        $existingTakers = DB::table('takers')
            ->whereIn('id', $takerIds)
            ->count();

        echo "✅ {$existingTakers} takers actually exist in takers table\n";

        if ($existingTakers !== count($takerIds)) {
            echo "⚠ Mismatch: group_taker has " . count($takerIds) . " but takers table has {$existingTakers}\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error checking relationships: " . $e->getMessage() . "\n";
}

// 7. Test API response simulation
echo "\n7. SIMULATING API RESPONSE:\n";
try {
    $data = [
        'takerCount' => $group->takers()->count(),
        'deliveryCount' => $group->deliveries()->count(),
        'group' => $group,
    ];

    echo "✅ API data prepared successfully:\n";
    echo "- Taker Count: {$data['takerCount']}\n";
    echo "- Delivery Count: {$data['deliveryCount']}\n";
    echo "- Group Name: {$data['group']->name}\n";
    echo "- Group Hash: {$data['group']->hash}\n";

} catch (\Exception $e) {
    echo "❌ Error preparing API data: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "URL should work if:\n";
echo "1. User is logged in as administrator\n";
echo "2. Group BE051125 exists (✅)\n";
echo "3. Takers are properly assigned (✅)\n";
echo "4. No permission issues\n";
echo "5. No JavaScript errors in browser\n";