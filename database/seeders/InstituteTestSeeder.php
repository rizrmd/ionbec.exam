<?php

namespace Database\Seeders;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Answer;
use App\Models\Takers\Group;
use App\Models\Exams\Question;
use Illuminate\Database\Seeder;
use App\Models\Categories\Category;
use App\Models\Deliveries\Delivery;

/**
 * Institute test.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class InstituteTestSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::where('name', '=', 'Unspecified')->get()->keyBy('id');
        $categoryIds = $categories->keys()->all();

        $mcqIds = [];
        $essayIds = [];

        $mcqs = require __DIR__.'/data/institute-test/multi-choice.php';
        foreach ($mcqs as $_mcq) {
            $mcq = $this->createQuestionItem($_mcq);
            $mcq->categories()->sync($categoryIds);
            $mcqIds[] = $mcq->id;
        }

        $essays = require __DIR__.'/data/institute-test/essay.php';

        foreach ($essays as $_essay) {
            $essay = $this->createQuestionItem($_essay);
            $essay->categories()->sync($categoryIds);
            $essayIds[] = $essay->id;
        }

        // create new exam
        $examMcq = new Exam();
        $examMcq->fill([
            'code' => 'IST-EXAM-MCQ',
            'name' => 'MCQ Institution Exam',
        ]);
        $examMcq->save();
        $examMcq->items()->sync($mcqIds);

        // create new exam
        $examEssay = new Exam();
        $examEssay->fill([
            'code' => 'IST-EXAM-ESSAY',
            'name' => 'Essay Institution Exam',
        ]);
        $examEssay->save();
        $examEssay->items()->sync($essayIds);

        $instituteGroups = Group::where('name', 'like', '%FKUNAIR%')->get();
        $takersId = [];

        foreach ($instituteGroups as $_ig) {
            foreach ($_ig->takers as $_t) {
                $takersId[] = $_t->id;
            }
        }

        $ig = new Group();
        $ig->fill([
            'name' => 'Institution Exam',
            'description' => '',
        ]);
        $ig->save();
        $ig->takers()->sync($takersId);

        $groups = [$ig];

        $groups[] = Group::where('name', 'Trialist')->first();

        foreach ($groups as $group) {
            $essayDelivery = new Delivery();
            $essayDelivery->fill([
                'group_id' => $ig->id,
                'exam_id' => $examEssay->id,
                'name' => $group->name.' - '.$examEssay->name,
                'duration' => 150,
                'scheduled_at' => '2017-07-03 10:30:00',
                'is_anytime' => ('Trialist' === $group->name) ? 1 : 0,
            ]);
            $essayDelivery->save();

            $mcqDelivery = new Delivery();
            $mcqDelivery->fill([
                'group_id' => $ig->id,
                'exam_id' => $examMcq->id,
                'name' => $group->name.' - '.$examMcq->name,
                'duration' => 120,
                'scheduled_at' => '2017-07-03 13:30:00',
                'is_anytime' => ('Trialist' === $group->name) ? 1 : 0,
            ]);
            $mcqDelivery->save();

            foreach ($group->takers as $taker) {
                $mcqDelivery->takers()->attach($taker->id, [
                    'token' => strtoupper(\Illuminate\Support\Str::random(5)),
                ]);

                $essayDelivery->takers()->attach($taker->id, [
                    'token' => strtoupper(\Illuminate\Support\Str::random(5)),
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
