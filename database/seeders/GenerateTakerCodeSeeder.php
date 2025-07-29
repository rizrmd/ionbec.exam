<?php

namespace Database\Seeders;

use App\Models\Takers\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenerateTakerCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $takerGroup = DB::table('group_taker')->get();

        foreach ($takerGroup as $data) {
            if (null === $data->taker_code) {
                $group = Group::query()->find($data->group_id);
                $slug = $group->code;
                if (null === $group->code) {
                    $slug = str($group->name)->slug();
                    $group->code = $slug;
                }

                $takerCode = str_pad($group->last_taker_code, 3, '0', STR_PAD_LEFT);
                DB::table('group_taker')
                    ->where('taker_id', $data->taker_id)
                    ->where('group_id', $data->group_id)
                    ->update(['taker_code' => $takerCode]);

                ++$group->last_taker_code;
                $group->save();
            }
        }
    }
}
