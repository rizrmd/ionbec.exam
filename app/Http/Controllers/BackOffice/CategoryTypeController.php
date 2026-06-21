<?php

namespace App\Http\Controllers\BackOffice;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Delete;
use App\Models\Categories\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Models\Categories\CategoryType;

class CategoryTypeController extends Controller
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

    #[Post('/back-office/category-type', name: 'back-office.category-type.store')]
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
        ]);

        $baseKey = Str::slug($request->input('label'));
        $key = $baseKey;
        $suffix = 2;
        while (CategoryType::query()->where('key', $key)->exists()) {
            $key = $baseKey.'-'.$suffix;
            $suffix++;
        }

        $maxOrder = (int) CategoryType::query()->max('order');

        $categoryType = new CategoryType();
        $categoryType->key = $key;
        $categoryType->label = $request->input('label');
        $categoryType->color = $request->input('color') ?: 'gray';
        $categoryType->order = $maxOrder + 1;
        $categoryType->save();

        return $this->actionSuccess(message: 'Category type created successfully.');
    }

    #[Put('/back-office/category-type/{category_type_hash}', name: 'back-office.category-type.update')]
    public function update(Request $request, CategoryType $categoryType): RedirectResponse
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $categoryType->label = $request->input('label');
        $categoryType->color = $request->input('color') ?: 'gray';
        if (null !== $request->input('order')) {
            $categoryType->order = $request->input('order');
        }
        $categoryType->save();

        return $this->actionSuccess(message: 'Category type updated successfully.');
    }

    #[Delete('/back-office/category-type/{category_type_hash}', name: 'back-office.category-type.destroy')]
    public function destroy(CategoryType $categoryType): RedirectResponse
    {
        if (Category::query()->where('type', $categoryType->key)->exists()) {
            return back()->withErrors(['type' => 'Cannot delete: categories still use this type.']);
        }

        $categoryType->delete();

        return $this->actionSuccess(message: 'Category type deleted successfully.');
    }
}
