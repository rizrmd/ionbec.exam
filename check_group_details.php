<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK DETAIL GROUP BE051125 ===\n";

// Cek group BE051125
$beGroup = DB::table('groups')->where('code', 'BE051125')->first();

if ($beGroup) {
    echo "Group BE051125 ditemukan:\n";
    echo "ID: {$beGroup->id}\n";
    echo "Name: '{$beGroup->name}'\n";
    echo "Code: '{$beGroup->code}'\n";
    echo "Description: '{$beGroup->description}'\n";
    echo "Last Taker Code: '{$beGroup->last_taker_code}'\n";
    echo "Created At: {$beGroup->created_at}\n";
    echo "Updated At: {$beGroup->updated_at}\n";
} else {
    echo "Group BE051125 tidak ditemukan!\n";
}

// Cek semua group yang mirip
echo "\n=== GROUP YANG MIRIP ===\n";
$similarGroups = DB::table('groups')
    ->where('name', 'ILIKE', '%BE051125%')
    ->orWhere('code', 'ILIKE', '%BE051125%')
    ->orderBy('id')
    ->get();

foreach ($similarGroups as $group) {
    echo "ID: {$group->id} | Name: '{$group->name}' | Code: '{$group->code}' | Desc: '{$group->description}'\n";
}

// Cek anggota group BE051125 (ID: 2)
echo "\n=== ANGGOTA GROUP BE051125 (ID: 2) ===\n";
$members = DB::table('group_taker')
    ->join('takers', 'group_taker.taker_id', '=', 'takers.id')
    ->where('group_taker.group_id', 2)
    ->select('takers.id', 'takers.reg', 'takers.name', 'takers.email', 'group_taker.taker_code')
    ->orderBy('takers.reg')
    ->get();

echo "Total anggota: " . count($members) . "\n";

foreach ($members as $member) {
    echo "ID: {$member->id} | Reg: {$member->reg} | Name: {$member->name} | Taker Code: {$member->taker_code}\n";
}

// Cek apakah ada anggota di group lain
echo "\n=== CEK GROUP LAIN ===\n";
$otherGroups = DB::table('group_taker')
    ->select('group_id', DB::raw('COUNT(*) as count'))
    ->where('group_id', '!=', 2)
    ->groupBy('group_id')
    ->orderBy('group_id')
    ->get();

foreach ($otherGroups as $other) {
    $groupName = DB::table('groups')->where('id', $other->group_id)->first();
    $name = $groupName ? $groupName->name : 'Unknown';
    echo "Group ID {$other->group_id} ('{$name}') memiliki {$other->count} anggota\n";
}