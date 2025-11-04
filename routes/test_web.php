<?php

use Illuminate\Http\Request;

Route::get('/test-group-update', function() {
    return response()->json([
        'message' => 'Test API working',
        'timestamp' => now(),
        'group' => \App\Models\Takers\Group::where('hash', '5bzO5NvE')->first(['name', 'code'])
    ]);
});