<?php

namespace Database\Seeders;

use App\Models\Takers\Taker;
use Illuminate\Database\Seeder;

/**
 * Takers table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class TakersTableSeeder extends Seeder
{
    /**
     * Seeder data.
     *
     * @var array
     */
    protected $data = [
        [
            'name' => 'Ritzky Pratomo Affan',
            'email' => 'ritz_qee@yahoo.com',
        ], [
            'name' => 'achmad fachrizal',
            'email' => 'rizalingua@yahoo.com',
        ], [
            'name' => 'Sumpada Priambudi',
            'email' => 'dadique.sans.frontieres@gmail.com',
        ], [
            'name' => 'Gestana Retaha Wardana',
            'email' => 'gestanawardana@gmail.com',
        ], [
            'name' => 'Christopher Simanjuntak',
            'email' => 'cas.orthosby@gmail.com',
        ], [
            'name' => 'Andi Bahauddin Fahmi',
            'email' => 'andibahauddin@gmail.com',
        ], [
            'name' => 'Aries Rakhmat Hidayat',
            'email' => 'ariesrakhmat@gmail.com',
        ], [
            'name' => 'Marquee Kenny Tumbelaka',
            'email' => 'mrqortho@gmail.com',
        ], [
            'name' => 'Ida Bagus Adhi Prayoga',
            'email' => 'dokter.prayoga@gmail.com',
        ],
    ];

    /**
     * Run database seeder.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $datum) {
            $taker = new Taker();
            $taker->fill($datum);

            $taker->save();
        }
    }
}
