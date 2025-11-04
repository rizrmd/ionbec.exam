<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUG GROUP UPDATE ===\n";

// Cek data group sekarang
$group = DB::table('groups')->where('id', 2)->first();

echo "Current Group Data:\n";
echo "ID: {$group->id}\n";
echo "Name: '{$group->name}'\n";
echo "Description: '{$group->description}'\n";
echo "Code: '{$group->code}'\n";
echo "Last Taker Code: '{$group->last_taker_code}'\n";
echo "Created At: {$group->created_at}\n";
echo "Updated At: {$group->updated_at}\n";

// Test update dengan data yang berbeda
echo "\n=== TESTING UPDATE ===\n";

$testDescription = "TEST UPDATE - " . date('Y-m-d H:i:s');
$affected = DB::table('groups')
    ->where('id', 2)
    ->update([
        'description' => $testDescription,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

echo "Update affected rows: {$affected}\n";

// Cek data setelah update
$updatedGroup = DB::table('groups')->where('id', 2)->first();

echo "\nData After Update:\n";
echo "ID: {$updatedGroup->id}\n";
echo "Name: '{$updatedGroup->name}'\n";
echo "Description: '{$updatedGroup->description}'\n";
echo "Code: '{$updatedGroup->code}'\n";
echo "Updated At: {$updatedGroup->updated_at}\n";

// Cek apakah ada constraint atau trigger yang mungkin menyebabkan masalah
echo "\n=== CEK CONSTRAINTS ===\n";

// Cek apakah ada unique constraint untuk name
$constraints = DB::select("
    SELECT
        tc.constraint_name,
        tc.constraint_type,
        kcu.column_name,
        ccu.table_name AS foreign_table_name,
        ccu.column_name AS foreign_column_name
    FROM information_schema.table_constraints AS tc
    JOIN information_schema.key_column_usage AS kcu
        ON tc.constraint_name = kcu.constraint_name
        AND tc.table_schema = kcu.table_schema
    LEFT JOIN information_schema.constraint_column_usage AS ccu
        ON ccu.constraint_name = tc.constraint_name
        AND ccu.table_schema = tc.table_schema
    WHERE tc.table_name = 'groups'
    AND tc.table_schema = 'public'
    AND tc.constraint_type = 'UNIQUE'
");

foreach ($constraints as $constraint) {
    echo "Constraint: {$constraint->constraint_name} | Column: {$constraint->column_name}\n";
}

// Cek model Group untuk melihat apakah ada issue
echo "\n=== MODEL TEST ===\n";

try {
    $groupModel = new \App\Models\Takers\Group();
    $groupData = $groupModel->find(2);

    if ($groupData) {
        echo "Model loaded successfully:\n";
        echo "Name: '{$groupData->name}'\n";
        echo "Description: '{$groupData->description}'\n";
        echo "Code: '{$groupData->code}'\n";

        // Test update melalui model
        $groupData->description = "MODEL UPDATE TEST - " . date('Y-m-d H:i:s');
        $saveResult = $groupData->save();

        echo "Model save result: " . ($saveResult ? 'SUCCESS' : 'FAILED') . "\n";

        // Reload dari database
        $reloaded = $groupModel->find(2);
        echo "After model update - Description: '{$reloaded->description}'\n";
    } else {
        echo "Group model not found for ID 2\n";
    }
} catch (\Exception $e) {
    echo "Model error: " . $e->getMessage() . "\n";
}

echo "\n=== REKOMENDASI ===\n";
echo "1. Jika update berhasil di database tapi tidak muncul di UI, masalah ada di frontend\n";
echo "2. Coba clear browser cache dan reload\n";
echo "3. Cek network tab di browser dev tools untuk melihat request yang dikirim\n";
echo "4. Cek JavaScript console untuk error\n";