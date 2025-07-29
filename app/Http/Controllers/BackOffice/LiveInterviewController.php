<?php

namespace App\Http\Controllers\BackOffice;

use Illuminate\Http\Request;
use App\Models\Exams\Question;
use App\Services\LiveInterview;
use App\Models\Attempts\Attempt;
use Dentro\Yalr\Attributes\Post;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class LiveInterviewController extends Controller
{
    #[Post('back-office/live-interview/{delivery_hash}/attempt/{attempt_hash}', name: 'back-office.live-interview.show-question')]
    public function showLiveQuestion(Request $request, Delivery $delivery, Attempt $attempt)
    {
        $request->validate([
            'question_hash' => [
                'required',
                new ExistsByHash(Question::class),
            ],
        ]);

        $question = Question::byHashOrFail($request->input('question_hash'));

        (new LiveInterview($delivery))->show($attempt, $question);

        return response()->json([
            'success' => true,
        ]);
    }
}
