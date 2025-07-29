<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Response;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Delete;
use App\Models\Categories\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Knowledge\Category\CategoryType;

class CategoryController extends Controller
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

    #[Get('/back-office/category', name: 'back-office.category.index')]
    public function index(Request $request): Response
    {
        $categoryQuery = Category::query();

        $categoryQuery->when($request->input('type') ?? false, function ($query, $type) {
            $query->where('type', $type);
        });

        $categoryQuery->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where('name', 'like', "%{$queryString}%");
        });

        return inertia('BackOffice/Category/Index', [
            'payload' => $categoryQuery->paginate($request->input('perPage', 15))->withQueryString(),
            'categoryTypes' => CategoryType::getOptions(),
            'types' => collect(CategoryType::getOptions())->map(static fn ($value, $key) => [
                'value' => $key,
                'label' => $value,
                'count' => Category::query()
//                    ->clone()
                    ->where('type', $key)->count(),
            ])->values(),
        ]);
    }

    #[Post('/back-office/category', name: 'back-office.category.store')]
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.implode(',', array_keys(CategoryType::getOptions())),
            'description' => 'nullable|string|max:255',
        ]);

        $category = new Category();
        $category->type = $request->input('type');
        $category->name = $request->input('name');
        $category->description = $request->input('description');
        $category->save();

        return $this->actionSuccess();
    }

    #[Put('/back-office/category/{category_hash}', name: 'back-office.category.update')]
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.implode(',', array_keys(CategoryType::getOptions())),
            'description' => 'nullable|string|max:255',
        ]);

        $category->type = $request->input('type');
        $category->name = $request->input('name');
        $category->description = $request->input('description');
        $category->save();

        return $this->actionSuccess();
    }

    #[Delete('/back-office/category/{category_hash}', name: 'back-office.category.destroy')]
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return $this->actionSuccess(message: 'Category deleted successfully.');
    }
}
