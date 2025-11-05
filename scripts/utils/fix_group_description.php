<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== UPDATE GROUP BE051125 DESCRIPTION ===\n";

// Update description group BE051125
$affected = DB::table('groups')
    ->where('id', 2)
    ->update([
        'description' => 'Group Kandidat Ujian BE051125 - 39 Peserta dari WIB dan WITA',
        'updated_at' => date('Y-m-d H:i:s')
    ]);

if ($affected > 0) {
    echo "✓ Group BE051125 description berhasil diupdate\n";
} else {
    echo "⚠ Tidak ada perubahan pada group BE051125\n";
}

// Cek ulang data group
$group = DB::table('groups')->where('id', 2)->first();

echo "\nData Group BE051125 setelah update:\n";
echo "ID: {$group->id}\n";
echo "Name: '{$group->name}'\n";
echo "Code: '{$group->code}'\n";
echo "Description: '{$group->description}'\n";
echo "Updated At: {$group->updated_at}\n";

// Cek apakah ada issue dengan nama yang sama di tempat lain
echo "\n=== CEK POTENSIAL KONFLIK ===\n";

// Cek apakah ada group dengan nama yang sama selain ID 2
$conflicts = DB::table('groups')
    ->where('name', 'BE051125')
    ->where('id', '!=', 2)
    ->get();

if (count($conflicts) > 0) {
    echo "⚠ Ditemukan konflik - group lain dengan nama yang sama:\n";
    foreach ($conflicts as $conflict) {
        echo "ID: {$conflict->id} | Name: '{$conflict->name}' | Code: '{$conflict->code}'\n";
    }
} else {
    echo "✓ Tidak ada konflik nama group\n";
}

// Cek apakah ada issue dengan frontend cache
echo "\n=== REKOMENDASI ===\n";
echo "1. Clear browser cache dan refresh halaman\n";
echo "2. Clear Laravel cache jika perlu:\n";
echo "   - php artisan cache:clear\n";
echo "   - php artisan view:clear\n";
echo "   - php artisan config:clear\n";
echo "3. Pastikan tidak ada JavaScript error di browser console\n";
echo "4. Coba edit group lain untuk memastikan fungsi edit bekerja\n";

// Buat script untuk clear cache
echo "\n=== RUN CACHE CLEAR ===\n";
try {
    // Clear cache
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');

    echo "✓ Cache berhasil dibersihkan\n";
} catch (\Exception $e) {
    echo "⚠ Error clearing cache: " . $e->getMessage() . "\n";
}