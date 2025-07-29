<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\Takers\Group;
use Illuminate\Database\Seeder;
use App\Models\Deliveries\Delivery;

/**
 * Deliveries table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class DeliveriesTableSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Group $group */
        $group = Group::query()->firstOrFail();

        $ids = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $session1 = new Delivery();
        $session1->fill([
            'group_id' => $group->id,
            'exam_id' => 1,
            'scheduled_at' => '2017-06-18 09:00:00',
            'duration' => 120,
        ]);
        $session1->save();

        foreach ($ids as $id) {
            $session1->takers()->attach($id, [
                'token' => Str::random(5),
            ]);
        }

        $session2 = new Delivery();
        $session2->fill([
            'group_id' => $group->id,
            'exam_id' => 2,
            'scheduled_at' => '2017-06-18 12:00:00',
            'duration' => 60,
        ]);
        $session2->save();

        foreach ($ids as $id) {
            $session2->takers()->attach($id, [
                'token' => Str::random(5),
            ]);
        }
    }
}
