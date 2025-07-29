<?php

namespace Database\Seeders;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use Illuminate\Database\Seeder;

class ExamMCQCheckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Exam::query()->get()->each(function (Exam $exam) {
            /** @var Item $item */
            $item = $exam->items()->first();

            /** @var Question $question */
            $question = $item?->questions()->first();

            if ('multiple-choice' == $question?->type->name) {
                $exam->update(['is_mcq' => 1]);
            } elseif ('essay' == $question?->type->name) {
                $exam->update(['is_mcq' => 0]);
            } else {
                $exam->update(['is_mcq' => null]);
            }
        });
    }
}
