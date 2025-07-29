<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use Illuminate\Http\Request;
use App\Models\Exams\Question;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Delete;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Knowledge\Exam\Item\ItemType;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class TestController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('allowed:administrator');
    }

    #[Get('back-office/test', name: 'back-office.test.index')]
    public function index(Request $request): Response
    {
        $examQuery = Exam::query();

        $examQuery->when($request->input('code') ?? false, function ($query, $queryString) {
            $query->where('code', 'like', "%{$queryString}%");
        });

        $examQuery->when($request->input('name') ?? false, function ($query, $queryString) {
            $query->where('name', 'like', "%{$queryString}%");
        });

        return Inertia::render('BackOffice/Test/Index', [
            'payload' => $examQuery
                ->latest()
                ->paginate($request->input('perPage', 15))
                ->withQueryString(),
        ]);
    }

    #[Post('back-office/test', name: 'back-office.test.store')]
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'code' => 'required|unique:exams|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'string|max:255',
        ]);

        $exam = new Exam();
        $exam->code = $request->code;
        $exam->name = $request->name;
        $exam->description = $request->description;

        // if interview is TRUE, then RANDOM and MCQ should be all FALSE.
        $exam->is_random = $request->is_interview ? false : $request->is_random;
        $exam->is_mcq = $request->is_interview ? false : ($request->is_mcq ?? false);
        $exam->is_interview = $request->is_interview ?? false;
        $exam->save();

        return $this->actionSuccess(message: 'Exam created successfully.');
    }

    private function examQuery($exam)
    {
        return Item::query()->whereHas('exams', function ($query) use ($exam) {
            $query->where('id', $exam->id);
        });
    }

    #[Get('back-office/test/{exam_hash}', name: 'back-office.test.detail')]
    public function detail(Request $request, Exam $exam): Response
    {
        $itemIds = DB::table('exam_item')->select('exam_id', 'item_id')->where('exam_id', $exam->id)->pluck('item_id');

        $totalVignette = Item::query()->where('is_vignette', true)->whereHas('exams', function ($query) use ($exam) {
            $query->where('id', $exam->id);
        })->count();

        $totalMultiple = Item::query()->where('type', ItemType::MULTIPLE_CHOICE)->whereHas('exams', function ($query) use ($exam) {
            $query->where('id', $exam->id);
        })->count();

        $totalQuestions = Question::query()->whereIn('item_id', $itemIds)->count();

        $itemsQuery = $this->examQuery($exam);

        $exam->load([
            'items' => function ($q) use ($request) {
                $q->when($request->input('query') ?? false, function ($query, $queryString) {
                    $query->where('title', 'like', "%{$queryString}%");
                });
                $q->orderBy('exam_item.order', 'ASC');
            },
            'items.questions',
        ]);

        $availableItems = Item::query()
            ->latest()
            ->whereNotIn('id', $itemsQuery->pluck('id'))
            ->whereIn('type', $exam->is_mcq ? [ItemType::MULTIPLE_CHOICE] : [ItemType::ESSAY, ItemType::INTERVIEW])
            ->get();

        return Inertia::render('BackOffice/Test/Detail', [
            'totalSets' => count($itemIds),
            'totalVignette' => $totalVignette,
            'totalMultiple' => $totalMultiple,
            'totalQuestions' => $totalQuestions,
            'exam' => $exam,
            'items' => $exam->items,
            'allItems' => Item::query()
                ->latest()
                ->whereNotIn('id', $itemsQuery->pluck('id'))
                ->whereIn('type', $exam->is_mcq ? [ItemType::MULTIPLE_CHOICE] : [ItemType::ESSAY, ItemType::INTERVIEW])
                ->get(),
        ]);
    }

    #[Put('/back-office/test/{exam_hash}', name: 'back-office.test.update')]
    public function update(Request $request, Exam $exam): \Illuminate\Http\RedirectResponse
    {
        $baseValidate = [
            'name' => 'required|string|max:255',
            'description' => 'string|max:255',
        ];
        if ($exam->code !== $request->code) {
            $baseValidate['code'] = 'required|unique:exams|string|max:255';
        }
        $request->validate($baseValidate);

        if ($exam->code !== $request->code) {
            $exam->code = $request->code;
        }
        $exam->name = $request->name;
        $exam->description = $request->description;
        $exam->is_random = $request->is_random;
        $exam->save();

        return $this->actionSuccess(message: 'Exam updated successfully.');
    }

    #[Delete('/back-office/text/{exam_hash}', name: 'back-office.test.destroy')]
    public function destroy(Exam $exam): \Illuminate\Http\RedirectResponse
    {
        $exam->delete();

        return $this->actionSuccess(route('back-office.test.index'), 'Exam deleted successfully.');
    }

    #[Post('back-office/test/{exam_hash}/add-question-set', name: 'back-office.test.add-question-set')]
    public function addQuestionSet(Request $request, Exam $exam): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'hash' => ['required', new ExistsByHash(Item::class)],
        ]);

        $id = Item::hashToId($request->hash);
        $exam->items()->attach($id);

        return $this->actionSuccess(message: 'Question Set added successfully.');
    }

    #[Post('back-office/test/{exam_hash}/remove-question-set', name: 'back-office.test.remove-question-set')]
    public function removeQuestionSet(Request $request, Exam $exam): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'hash' => ['required', new ExistsByHash(Item::class)],
        ]);

        $id = Item::hashToId($request->hash);
        $exam->items()->detach($id);

        return $this->actionSuccess(message: 'Question Set removed successfully.');
    }

    #[Get('back-office/test/{exam_hash}/pdf', name: 'back-office.test.pdf')]
    public function testPDF(Request $request, Exam $exam): Response
    {
        return Inertia::render('BackOffice/Test/PDF', [
            'exam' => $exam->load([
                'items' => function ($q) {
                    $q->orderBy('order', 'DESC');
                },
                'items.questions.answers',
                'items.attachments',
            ]),
            'itemTypes' => ItemType::getOptions(),
        ]);
    }

    #[Post('back-office/test/{exam_hash}/reorder', name: 'back-office.test.reorder')]
    public function reorder(Request $request, Exam $exam): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'hashes' => ['required', 'array'],
        ]);

        foreach ($request->hashes as $key => $hash) {
            $id = Item::hashToId($hash);
            if ($id) {
                DB::table('exam_item')
                    ->where('exam_id', $exam->id)
                    ->where('item_id', $id)
                    ->update([
                        'order' => $key + 1,
                    ]);
                //                dd($examItem);
            }
        }

        return $this->actionSuccess(message: 'Successfully Reordered Question.');
    }
}
