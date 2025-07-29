<?php

namespace Database\Seeders;

use App\Models\Accounts\User;
use Illuminate\Database\Seeder;

/**
 * Committee table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class CommitteeDataSeeder extends Seeder
{
    /**
     * Seeder data.
     *
     * @var array
     */
    protected $data = [
        [
            'name' => 'Aryadi Kurniawan, dr.',
            'username' => 'aryadikurniawan ',
            'email' => 'aryadikurniawan @ionbec.com',
            'password' => 'aryadikurniawan',
            'gender' => 'male',
        ], [
            'name' => 'Satria Sakti, dr.',
            'username' => 'satriasakti ',
            'email' => 'satriasakti @ionbec.com',
            'password' => 'satriasakti',
            'gender' => 'male',
        ], [
            'name' => 'Magetsari, dr.',
            'username' => 'magetsari',
            'email' => 'magetsari@ionbec.com',
            'password' => 'magetsari',
            'gender' => 'male',
        ], [
            'name' => 'Hermawan, dr.',
            'username' => 'hermawan',
            'email' => 'hermawan@ionbec.com',
            'password' => 'hermawan',
            'gender' => 'male',
        ], [
            'name' => 'Mujaddid, dr.',
            'username' => 'mujaddid',
            'email' => 'mujaddid@ionbec.com',
            'password' => 'mujaddid',
            'gender' => 'male',
        ], [
            'name' => 'Syaifullah Asmiragani, dr.',
            'username' => 'syaifullahasmiragani',
            'email' => 'syaifullahasmiragani@ionbec.com',
            'password' => 'syaifullahasmiragani',
            'gender' => 'male',
        ], [
            'name' => 'agaspketaren, dr.',
            'username' => 'agaspketaren',
            'email' => 'agaspketaren@ionbec.com',
            'password' => 'agaspketaren',
            'gender' => 'male',
        ], [
            'name' => 'Mouli Edward, dr.',
            'username' => 'mouliedward',
            'email' => 'mouliedward@ionbec.com',
            'password' => 'mouliedward',
            'gender' => 'male',
        ], [
            'name' => 'I Gede Eka Wiratnaya, dr.',
            'username' => 'igedeekawiratnaya',
            'email' => 'igedeekawiratnaya@ionbec.com',
            'password' => 'igedeekawiratnaya',
            'gender' => 'male',
        ], [
            'name' => 'Sakti, dr.',
            'username' => 'sakti',
            'email' => 'sakti@ionbec.com',
            'password' => 'sakti',
            'gender' => 'male',
        ],
    ];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $datum) {
            $user = User::where('username', $datum['username'])->first();
            $user = ($user instanceof User) ? $user : new User();
            $user->fill($datum);
            $user->save();

            $this->command->info('Success: `'.$user->name.'` has been successfully added!');
        }
    }
}
