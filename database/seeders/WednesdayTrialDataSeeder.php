<?php

namespace Database\Seeders;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Answer;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use App\Models\Exams\Question;
use Illuminate\Database\Seeder;
use App\Models\Categories\Category;
use App\Models\Deliveries\Delivery;

/**
 * Wednesday trial seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class WednesdayTrialDataSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::where('name', '=', 'Unspecified')->get()->keyBy('id');
        $categoryIds = $categories->keys()->all();
        $mcqIds = [];
        $essayIds = [];

        $mcqs = require __DIR__.'/data/wednesday-trial/multi-choice.php';
        foreach ($mcqs as $_mcq) {
            $mcq = $this->createQuestionItem($_mcq);
            $mcq->categories()->sync($categoryIds);
            $mcqIds[] = $mcq->id;
        }

        $essays = require __DIR__.'/data/wednesday-trial/essay.php';

        foreach ($essays as $_essay) {
            $essay = $this->createQuestionItem($_essay);
            $essay->categories()->sync($categoryIds);
            $essayIds[] = $essay->id;
        }

        // create new exam
        $examMcq = new Exam();
        $examMcq->fill([
            'code' => 'TRIAL-MCQ-WED',
            'name' => 'MCQ National Trial',
        ]);
        $examMcq->save();
        $examMcq->items()->sync($mcqIds);

        $examEssay = new Exam();
        $examEssay->fill([
            'code' => 'TRIAL-MCQ-WED',
            'name' => 'Essay National Trial',
        ]);
        $examEssay->save();
        $examEssay->items()->sync($essayIds);

        $takers = require __DIR__.'/data/wednesday-trial/takers.php';
        foreach ($takers as $_group) {
            $group = new Group();
            $group->fill([
                'name' => $_group['name'],
                'description' => $_group['description'],
            ]);
            $group->save();

            $takersIds = [];
            foreach ($_group['takers'] as $_taker) {
                $taker = new Taker();
                $taker->fill($_taker);
                $taker->save();
                $taker->reg = '2017-'.str_pad($taker->id, 5, '0', STR_PAD_LEFT);
                $taker->save();

                $takersIds[] = $taker->id;
            }

            $group->takers()->sync($takersIds);

            $essayDelivery = new Delivery();
            $essayDelivery->fill([
                'group_id' => $group->id,
                'exam_id' => $examEssay->id,
                'name' => $group->name.'-'.$examEssay->name,
                'duration' => 30,
                'scheduled_at' => '2017-06-21 09:00:00',
                'is_anytime' => ('Trialist' === $group->name) ? 1 : 0,
            ]);
            $essayDelivery->save();
            foreach ($takersIds as $id) {
                $essayDelivery->takers()->attach($id, [
                    'token' => \Illuminate\Support\Str::random(5),
                ]);
            }

            $mcqDelivery = new Delivery();
            $mcqDelivery->fill([
                'group_id' => $group->id,
                'exam_id' => $examMcq->id,
                'name' => $group->name.'-'.$examMcq->name,
                'duration' => 20,
                'scheduled_at' => '2017-06-21 09:40:00',
                'is_anytime' => ('Trialist' === $group->name) ? 1 : 0,
            ]);
            $mcqDelivery->save();
            foreach ($takersIds as $id) {
                $mcqDelivery->takers()->attach($id, [
                    'token' => \Illuminate\Support\Str::random(5),
                ]);
            }
        }
    }

    protected function createQuestionItem($inputs = [])
    {
        $item = new Item();
        $item->fill([
            'title' => $inputs['title'],
            'content' => isset($inputs['path']) ? file_get_contents(__DIR__.$inputs['path']) : $inputs['content'],
        ]);
        $item->save();
        $this->command->info('Item created:`'.$item->title.'`');

        foreach ($inputs['questions'] as $_question) {
            $question = new Question();
            $question->fill([
                'item_id' => $item->id,
                'type' => $_question['type'],
                'question' => isset($_question['path']) ? file_get_contents(__DIR__.$_question['path']) : $_question['question'],
            ]);
            $question->save();
            $this->command->info('Question created: `'.$question->type?->toJson().' #'.$question->id.'`');

            foreach ($_question['answers'] as $_answer) {
                $answer = new Answer();
                $answer->fill([
                    'question_id' => $question->id,
                    'is_correct_answer' => $_answer['is_correct_answer'],
                    'answer' => $_answer['answer'],
                ]);
                $answer->save();
            }
        }

        return $item;
    }
}
