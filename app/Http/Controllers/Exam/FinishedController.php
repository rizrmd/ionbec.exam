<?php

namespace App\Http\Controllers\Exam;

use Inertia\Inertia;
use App\Jobs\CalculateScore;
use App\Models\Takers\Taker;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Services\ExamAccessService;
use Illuminate\Support\Facades\Session;

class FinishedController extends Controller
{
    #[Get('/exam/finished', name: 'exam.finished')]
    public function index()
    {
        $dataSession = Session::get('exam');

        if (is_null($dataSession) && ! Session::has('exam_finished')) {
            return redirect('/');
        }

        if (is_array($dataSession) && null !== ($dataSession['admin'] ?? null)) {
            Session::forget('exam');

            return redirect('/');
        }

        $resolved = app(ExamAccessService::class)->resolveSession();
        $finished = Session::get('exam_finished', []);
        $deliveryId = $resolved['delivery']->id ?? ($finished['delivery_id'] ?? null);
        $takerId = $resolved['taker']->id ?? ($finished['taker_id'] ?? null);
        $delivery = $deliveryId ? Delivery::withoutGlobalScope(\App\Scopes\ClientScope::class)->find($deliveryId) : null;
        $taker = $takerId ? Taker::withoutGlobalScope(\App\Scopes\ClientScope::class)->find($takerId) : null;

        return Inertia::render('Exam/Finished', [
            'delivery' => $delivery,
            'taker' => $taker,
        ]);
    }

    #[Post('/exam/finish', name: 'exam.finish')]
    public function finish()
    {
        $attempt = app(ExamAccessService::class)->finishCurrentAttempt();

        if ($attempt) {
            try {
                CalculateScore::dispatch($attempt->fresh());
            } catch (\Throwable $exception) {
                \Log::error('Unable to dispatch score calculation after exam finish.', [
                    'attempt_id' => $attempt->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'redirect' => route('exam.finished'),
        ]);
    }
}
