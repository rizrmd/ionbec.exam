<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Exams\Question;
use Dentro\Yalr\Attributes\Get;
use App\Models\Categories\Category;
use App\Http\Controllers\Controller;
use App\Knowledge\Exam\Item\ItemType;
use App\Knowledge\Category\CategoryType;
use Illuminate\Database\Eloquent\Builder;

class QuestionPackController extends Controller
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

    #[Get('back-office/question-pack', name: 'back-office.question-pack.index')]
    public function index(Request $request): Response
    {
        $questionQuery = Question::query();

        $categoryType = CategoryType::getOptions();
        $categoriesValue = [];
        foreach ($categoryType as $key => $type) {
            $categoriesValue[$key] = Category::query()->where('type', $key)->get();
        }

        $questionQuery->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where('question', 'like', "%{$queryString}%")
                ->orWhereHas('item', fn (Builder $query) => $query
                    ->where('title', 'like', "%{$queryString}%"));
        });

        $questionQuery->when($request->input('question_type') ?? false, function ($query, $type) {
            $query->whereHas('item', fn (Builder $query) => $query
                ->where('type', $type));
        });

        $questionQuery->when($request->input('type') ?? false, function ($query, $type) {
            $query->whereHas('categories', function ($query) use ($type) {
                $query->where('type', $type);
            });
        });

        $questionQuery->where(function (Builder $query) use ($request) {
            if ($diseaseGroup = $request->input('disease_group')) {
                $query->whereHas('categories', fn (Builder $query) => $query
                    ->where('id', Category::hashToId($diseaseGroup)));
            }

            if ($regionGroup = $request->input('region_group')) {
                $query->whereHas('categories', fn (Builder $query) => $query
                    ->where('id', Category::hashToId($regionGroup)));
            }

            if ($specificPart = $request->input('specific_part')) {
                $query->whereHas('categories', fn (Builder $query) => $query
                    ->where('id', Category::hashToId($specificPart)));
            }

            if ($typicalGroup = $request->input('typical_group')) {
                $query->whereHas('categories', fn (Builder $query) => $query
                    ->where('id', Category::hashToId($typicalGroup)));
            }
        });

        return Inertia::render('BackOffice/QuestionPack/Index', [
            'stats' => [
                'totalData' => Question::query()->count(),
                'totalMultipleChoices' => $questionQuery->clone()->whereHas('item', fn ($q) => $q->where('type', ItemType::MULTIPLE_CHOICE))->count(),
                'totalEssay' => $questionQuery->clone()->whereHas('item', fn ($q) => $q->where('type', ItemType::ESSAY))->count(),
                'totalFiltered' => $questionQuery->clone()->count(),
            ],
            'typeOptions' => ItemType::getOptions(),
            'categoryType' => $categoryType,
            'categoriesValue' => $categoriesValue,
            'payload' => $questionQuery->with(['item', 'categories'])->latest()->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }
}
