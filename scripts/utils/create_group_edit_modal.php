<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ANALISIS GROUP EDIT ISSUE ===\n";

// Cek apakah group data berubah setelah Anda edit
$group = DB::table('groups')->where('id', 2)->first();

echo "Current Group Data (ID 2):\n";
echo "Name: '{$group->name}'\n";
echo "Description: '{$group->description}'\n";
echo "Code: '{$group->code}'\n";
echo "Updated At: {$group->updated_at}\n";

// Cek log perubahan dengan timestamp
echo "\n=== CEK PERUBAHAN TERAKHIR ===\n";
$recentUpdates = DB::table('groups')
    ->select('id', 'name', 'description', 'updated_at')
    ->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
    ->orderBy('updated_at', 'desc')
    ->get();

foreach ($recentUpdates as $update) {
    echo "ID: {$update->id} | Name: '{$update->name}' | Description: '{$update->description}' | Updated: {$update->updated_at}\n";
}

echo "\n=== DIAGNOSIS ===\n";
echo "1. Jika data di database sudah berubah tapi UI tidak, masalahnya di Vue.js state management\n";
echo "2. Vue component Index.vue tidak memiliki edit functionality - hanya create\n";
echo "3. Kemungkinan besar edit group dilakukan melalui halaman lain atau modal yang terpisah\n";

// Test API endpoint response
echo "\n=== TEST API RESPONSE ===\n";

// Coba test apakah API endpoint response benar
try {
    // Simulasi response seperti yang dikirim ke Vue
    $groupData = DB::table('groups')
        ->with('takers')
        ->where('id', 2)
        ->first();

    echo "API Response Simulation:\n";
    echo "Group Name: '{$groupData->name}'\n";
    echo "Group Description: '{$groupData->description}'\n";
    echo "Takers Count: " . (isset($groupData->takers) ? count($groupData->takers) : 0) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== REKOMENDASI ===\n";
echo "1. Cari file Vue component yang menangani edit group\n";
echo "2. Check apakah ada halaman edit terpisah: /back-office/group/{hash}/edit\n";
echo "3. Check apakah ada hidden edit modal di Index.vue\n";
echo "4. Clear browser cache dan reload\n";
echo "5. Check network tab untuk melihat request saat edit group\n";