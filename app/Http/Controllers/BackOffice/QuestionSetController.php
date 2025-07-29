<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use Illuminate\Support\Str;
use App\Models\Exams\Answer;
use Illuminate\Http\Request;
use App\Models\Exams\Question;
use Dentro\Yalr\Attributes\Get;
use Illuminate\Validation\Rule;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Delete;
use JetBrains\PhpStorm\ArrayShape;
use App\Models\Categories\Category;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Knowledge\Exam\Item\ItemType;
use Illuminate\Http\RedirectResponse;
use Intervention\Image\Facades\Image;
use App\Models\Attachments\Attachment;
use Illuminate\Support\Facades\Storage;
use App\Knowledge\Category\CategoryType;
use App\Models\Attempts\AttemptQuestion;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class QuestionSetController extends Controller
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

    #[ArrayShape(['type' => 'array', 'title' => 'string', 'is_vignette' => 'string', 'is_random' => 'string', 'tests' => 'string', 'tests.*' => '\Veelasky\LaravelHashId\Rules\ExistsByHash[]'])]
    private function baseValidation(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(ItemType::getOptions()))],
            'title' => 'required|string|max:255',
            'is_vignette' => 'boolean',
            'is_random' => 'boolean',
            'tests' => 'array',
            'tests.*' => [new ExistsByHash(Exam::class)],
        ];
    }

    #[Get('back-office/question-set', name: 'back-office.question-set.index')]
    public function index(Request $request): Response
    {
        $itemQuery = Item::query();
        $questionQuery = Question::query();

        $multipleChoice = $questionQuery->clone()->where('type', ItemType::MULTIPLE_CHOICE)->count();
        $essay = $questionQuery->clone()->where('type', ItemType::ESSAY)->count();
        $vignette = $itemQuery->clone()->where('is_vignette', true)->count();
        $nonVignette = $itemQuery->clone()->where('is_vignette', false)->count();

        $itemQuery->when($request->input('type') ?? false, function ($query, $type) {
            $query->where('type', $type);
        });

        $itemQuery->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where('title', 'like', "%{$queryString}%");
        });

        $itemQuery->when($request->input('is_vignette') ?? false, function ($query, $vignette) {
            $query->where('is_vignette', 'true' === $vignette);
        });

        $itemQuery->latest('created_at');

        return inertia('BackOffice/QuestionSet/Index', [
            'countMultipleChoice' => $multipleChoice,
            'countEssay' => $essay,
            'countVignette' => $vignette,
            'countNonVignette' => $nonVignette,
            'tests' => Exam::all(),
            'typeOptions' => ItemType::getOptions(),
            'payload' => $itemQuery->with('questions')->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }

    private function getCategories()
    {
        $categories = [];
        foreach (CategoryType::getOptions() as $key => $name) {
            $categories[$key]['name'] = $name;
            $categories[$key]['options'] = Category::query()->where('type', $key)->get();
        }

        return $categories;
    }

    #[Get('back-office/question-set/create', name: 'back-office.question-set.create')]
    public function create(Request $request): Response
    {
        return Inertia::render('BackOffice/QuestionSet/Create', [
            'categories' => $this->getCategories(),
            'tests' => Exam::all(),
            'typeOptions' => ItemType::getOptions(['simple']),
        ]);
    }

    #[Get('back-office/question-set/{item_hash}', name: 'back-office.question-set.detail')]
    public function detail(Request $request, Item $item): Response
    {
        $questionQuery = Question::query()->where('item_id', $item->id)->with(['answers', 'categories'])->orderBy('order', 'ASC');
        $questions = $questionQuery->clone()->get();

        $questionIds = $questionQuery->clone()->pluck('id');
        $chartData = [];
        foreach ($item->exams as $exam) {
            $deliveries = Delivery::query()->where('exam_id', $exam->id)->orderBy('scheduled_at', 'DESC')->limit(5)->get();
            $correctness = [];
            $labels = [];
            foreach ($deliveries as $index => $delivery) {
                if ($index < 5) {
                    $attemptQuestQuery = AttemptQuestion::query()->whereHas('attempt', function ($q) use ($delivery) {
                        $q->where('delivery_id', $delivery->id);
                    })->whereIn('question_id', $questionIds);
                    $result = 0;
                    $total = $attemptQuestQuery->clone()->count();
                    $correct = $attemptQuestQuery->clone()->where('is_correct', true)->count();
                    if ($total >= 1) {
                        $result = round($correct * 100 / $total, 2);
                    }
                    $labels[] = $delivery->name ?? '-';
                    $correctness[] = $result;
                }
            }

            $chartData[] = [
                'exam' => $exam,
                'countDelivery' => count($deliveries->toArray()),
                'data' => [
                    'labels' => $labels,
                    'correctness' => $correctness,
                ],
            ];
        }

        return Inertia::render('BackOffice/QuestionSet/Detail', [
            'item' => $item->load(['exams', 'attachments']),
            'categories' => $this->getCategories(),
            'tests' => Exam::all(),
            'typeOptions' => ItemType::getOptions(['simple']),
            'questions' => $questions,
            'chartData' => $chartData,
        ]);
    }

    #[Post('back-office/question-set/', name: 'back-office.question-set.store')]
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->baseValidation());

        $tests = [];
        foreach ($request->tests as $hash) {
            $tests[] = Exam::hashToId($hash);
        }

        $item = new Item();
        $item->title = $request->title;
        $item->type = $request->type;
        $item->is_vignette = $request->boolean('is_vignette');
        $item->is_random = $request->boolean('is_random');
        $item->save();

        $item->exams()->attach($tests);

        if ($request->auto_redirect) {
            return redirect()->route('back-office.question-set.detail', ['item_hash' => Item::idToHash($item->id)]);
        }

        return $this->actionSuccess(message: 'Question Set created successfully.');
    }

    #[Post('back-office/question-set/create-or-update', name: 'back-office.question-set.create-or-update')]
    public function createOrUpdate(Request $request): RedirectResponse
    {
        $request->validate(array_merge($this->baseValidation(), [
            'hash' => 'nullable',
            'questions' => 'array',
            'questions.*.is_random' => 'boolean',
            'questions.*.answers' => 'array',
            'deleted_questions' => 'array',
            'deleted_answers' => 'array',
        ]));

        $item = (null !== $request->hash) ? Item::byHash($request->hash) : new Item();

        // Start Update Item
        $tests = [];
        foreach ($request->tests as $hash) {
            $tests[] = Exam::hashToId($hash);
        }

        $oldVignette = $item->is_vignette;

        $item->title = $request->title;
        $item->type = $request->type;
        $item->content = $request->vignette_case;
        $item->is_vignette = 'true' == $request->is_vignette;
        $item->is_random = 'true' == $request->is_random;
        $item->save();

        if ($oldVignette && ! $request->is_vignette) {
            $questions = Question::query()->where('item_id', $item->id)->with(['answers'])->get();
            foreach ($questions as $question) {
                $question->answers()->delete();
                $question->categories()->detach();
                $question->delete();
            }
        }

        $item->exams()->sync($tests);
        // END Update Item

        foreach ($request->deleted_questions as $hash) {
            $question = Question::byHash($hash);
            $question->answers()->delete();
            $question->delete();
        }

        foreach ($request->questions as $index => $data) {
            $question = null === $data['hash'] ? new Question() : Question::byHash($data['hash']);
            $question->item_id = $item->id;
            $question->type = $item->type;
            $question->question = $data['question'];
            $question->is_random = $data['is_random'];
            $question->order = $index + 1;
            $question->save();

            $categoriesData = [];
            if (null !== $data['disease_group']) {
                $categoriesData[] = Category::byHash($data['disease_group'])->id;
            }
            if (null !== $data['region_group']) {
                $categoriesData[] = Category::byHash($data['region_group'])->id;
            }
            if (null !== $data['typical_group']) {
                $categoriesData[] = Category::byHash($data['typical_group'])->id;
            }
            if (null !== $data['specific_part']) {
                $categoriesData[] = Category::byHash($data['specific_part'])->id;
            }
            $question->categories()->sync($categoriesData);

            foreach ($data['answers'] as $ans) {
                //                if (!($ans['hash'] === null && $ans['answer'] === null)) {
                $answer = (null === $ans['hash']) ? new Answer() : Answer::byHash($ans['hash']);
                $answer->question_id = $question->id;
                $answer->answer = $ans['answer'];
                $answer->is_correct_answer = $ans['is_correct_answer'];
                $answer->save();
                //                }
            }
        }

        foreach ($request->deleted_answers as $hash) {
            Answer::byHash($hash)->delete();
        }

        $successMsg = 'Question Set updated successfully.';
        if (null === $request->hash) {
            return $this->actionSuccess(to: route('back-office.question-set.detail', $item->hash), message: $successMsg);
        }

        return $this->actionSuccess(message: $successMsg);
    }

    #[Delete('/back-office/question-set/{item_hash}', name: 'back-office.question-set.destroy')]
    public function destroy(Item $item): RedirectResponse
    {
        $item->delete();

        return $this->actionSuccess(route('back-office.question-set.index'), message: 'Question Set deleted successfully.');
    }

    #[Post('/back-office/question-set/{item_hash}/attach', name: 'back-office.question-set.attach')]
    public function attach(Item $item, Request $request): RedirectResponse
    {
        $request->validate(['image' => 'required|mimes:jpeg,jpg,png|max:2048']);

        if ($request->file('image')) {
            $user = auth()->user();
            $file = $request->file('image');

            $mime = $file->getClientOriginalExtension();
            $taker = new TestTakerController();
            $fileName = $item->hash.'-'.$taker->generateRandomString().'.'.$mime;

            $image = Image::make($file);

            $path = 'attachments/'.$fileName;

            $storage = Storage::disk('local')->put($path, (string) $image->encode());

            $item->attachments()->create([
                'id' => Str::uuid(),
                'uploaded_by' => $user->getAuthIdentifier(),
                'path' => $path,
                'mime' => $mime,
            ]);
        }

        return $this->actionSuccess(message: 'Image uploaded successfully.');
    }

    #[Post('/back-office/question-set/{item_hash}/delete-attach', name: 'back-office.question-set.delete-attach')]
    public function deleteAttach(Item $item, Request $request): RedirectResponse
    {
        $request->validate(['id' => 'required']);
        $attachment = Attachment::query()->where('id', $request->id)->first();
        if ($attachment) {
            $item->attachments()->detach($attachment);
            if (Storage::exists($attachment->path)) {
                Storage::delete($attachment->path);
            }
            $attachment->delete();
        }

        return $this->actionSuccess(message: 'Image removed successfully.');
    }
}
