<?php

namespace Database\Seeders;

use App\Models\Accounts\Role;
use Illuminate\Database\Seeder;

/**
 * @author      veelasky <veelasky@gmail.com>
 */
class RoleTableSeeder extends Seeder
{
    protected $roles = [
        [
            'name' => 'Administrator',
            'slug' => 'administrator',
            'description' => 'Super Administrator',
        ], [
            'name' => 'Scorer / Committee',
            'slug' => 'scorer',
            'description' => 'Test Scorer and/or committee',
        ],
    ];

    public function run()
    {
        foreach ($this->roles as $roleData) {
            $role = new Role();
            $role->fill($roleData);
            $role->save();
        }
    }
}
