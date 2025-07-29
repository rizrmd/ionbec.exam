<?php

namespace Database\Seeders;

use App\Models\Takers\Group;
use Illuminate\Database\Seeder;

/**
 * Groups table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class GroupsTableSeeder extends Seeder
{
    /**
     * Seeder data.
     *
     * @var array
     */
    protected $data = [
        [
            'name' => 'Trial',
            'description' => null,
        ],
    ];

    /**
     * Run Seeder data.
     *
     * @return void
     */
    public function run()
    {
        $ids = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        foreach ($this->data as $datum) {
            $group = new Group();
            $group->fill($datum);
            $group->save();

            $group->takers()->sync($ids);
        }
    }
}
