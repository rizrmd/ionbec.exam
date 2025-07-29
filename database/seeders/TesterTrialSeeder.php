<?php

namespace Database\Seeders;

use App\Models\Exams\Exam;
use Illuminate\Database\Seeder;
use App\Models\Deliveries\Delivery;

/**
 * Tester Trial Seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class TesterTrialSeeder extends Seeder
{
    public function run()
    {
        $takerIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $deliveryMcq = new Delivery();
        $examMcq = Exam::where('code', '=', 'TRIAL-MCQ-2')->first();
        $deliveryMcq->fill([
            'exam_id' => $examMcq->id,
            'scheduled_at' => '2017-06-20 01:00:00',
            'duration' => 120,
        ]);
        $deliveryMcq->save();

        foreach ($takerIds as $id) {
            $deliveryMcq->takers()->attach($id, [
                'token' => \Illuminate\Support\Str::random(5),
            ]);
        }
    }
}
