<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Takers\Group;

echo "=== DEBUG GROUP ROUTE ISSUE ===\n";

// Cek group dengan hash 5bzO5NvE
echo "1. Mencari group dengan hash '5bzO5NvE'...\n";

$groupId = null;
try {
    $groupId = Group::hashToId('5bzO5NvE');
    echo "Hash '5bzO5NvE' → ID: {$groupId}\n";
} catch (\Exception $e) {
    echo "Error decoding hash: " . $e->getMessage() . "\n";
}

if ($groupId) {
    // Cek apakah group ada
    $group = Group::find($groupId);
    if ($group) {
        echo "Group ditemukan:\n";
        echo "- ID: {$group->id}\n";
        echo "- Name: {$group->name}\n";
        echo "- Code: {$group->code}\n";
        echo "- Hash: {$group->hash}\n";

        // Cek apakah group BE051125 (ID: 2) hash nya sama
        $beGroup = Group::find(2);
        echo "\nGroup BE051125 (ID: 2):\n";
        echo "- Name: {$beGroup->name}\n";
        echo "- Hash: {$beGroup->hash}\n";

        if ($beGroup->hash === '5bzO5NvE') {
            echo "✅ Hash sama! Group BE051125 ditemukan\n";
        } else {
            echo "⚠ Hash berbeda. Hash BE051125: '{$beGroup->hash}'\n";
        }

        // Cek anggota group
        $takerCount = $group->takers()->count();
        echo "- Jumlah anggota: {$takerCount}\n";

    } else {
        echo "❌ Group dengan ID {$groupId} tidak ditemukan\n";
    }
} else {
    echo "❌ Tidak dapat decode hash '5bzO5NvE'\n";
}

// Cek semua group yang ada
echo "\n2. Semua groups yang ada:\n";
$allGroups = Group::all();
foreach ($allGroups as $group) {
    echo "- ID: {$group->id} | Name: {$group->name} | Hash: {$group->hash}\n";
}

// Test route generation
echo "\n3. Test route generation:\n";
if (isset($beGroup)) {
    try {
        $route = route('back-office.group.taker', ['group_hash' => $beGroup->hash]);
        echo "Route yang benar: {$route}\n";
    } catch (\Exception $e) {
        echo "Error generate route: " . $e->getMessage() . "\n";
    }
}