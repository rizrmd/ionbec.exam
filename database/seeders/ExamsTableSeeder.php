<?php

namespace Database\Seeders;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use Illuminate\Database\Seeder;

/**
 * Exams table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class ExamsTableSeeder extends Seeder
{
    public function run()
    {
        $mcq = Item::where('id', '<=', 23)->get();
        $mcq = $mcq->keyBy('id');
        $mcgIds = $mcq->keys()->all();

        $examMcq = new Exam();
        $examMcq->fill([
            'code' => 'TRIAL-MCQ',
            'name' => 'MCQ Trial',
        ]);
        $examMcq->save();
        $examMcq->items()->sync($mcgIds);

        // -------------------------
        $essay = Item::where('id', '>', 23)->get();
        $essay = $essay->keyBy('id');
        $essayIds = $essay->keys()->all();

        $examEssay = new Exam();
        $examEssay->fill([
            'code' => 'TRIAL-ESSAY',
            'name' => 'Essay Trial',
        ]);
        $examEssay->save();
        $examEssay->items()->sync($essayIds);
    }
}
