<?php

use Illuminate\Support\Facades\Route;
use App\Models\Exams\Item;

Route::get('/check-env-debug', function () {
    $data = [
        'APP_ENV' => env('APP_ENV'),
        'APP_URL' => env('APP_URL'),
        'DB_DATABASE' => env('DB_DATABASE'),
        'DB_HOST' => env('DB_HOST'),
        'Actual_DB' => DB::connection()->getDatabaseName(),
        'Total_Items' => Item::count(),
        'Items_Under_16' => Item::where('id', '<', 16)->count(),
        'First_5_Items' => Item::orderBy('id')->limit(5)->get(['id', 'title'])->toArray()
    ];

    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
});

Route::get('/delete-qs-production', function () {
    if (env('APP_ENV') === 'local') {
        return response()->json(['error' => 'This endpoint is for production only'], 403);
    }

    $deletedIds = range(1, 15);
    $itemsToDelete = Item::whereIn('id', $deletedIds)->get();

    if ($itemsToDelete->isEmpty()) {
        return response()->json(['message' => 'No question sets found with IDs 1-15']);
    }

    DB::beginTransaction();
    try {
        foreach ($itemsToDelete as $item) {
            $questions = \App\Models\Exams\Question::where('item_id', $item->id)->get();
            foreach ($questions as $question) {
                \App\Models\Exams\Answer::where('question_id', $question->id)->delete();
                $question->categories()->detach();
                $question->delete();
            }
            $item->exams()->detach();
            $item->delete();
        }
        DB::commit();

        return response()->json([
            'message' => 'Successfully deleted question sets 1-15',
            'deleted_count' => $itemsToDelete->count(),
            'deleted_items' => $itemsToDelete->pluck('title')->toArray()
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
});