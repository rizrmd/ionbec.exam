<?php

namespace App\Http\Controllers\Exam;

use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use Illuminate\Support\Facades\DB;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class ExamController extends Controller
{
    #[Post('/exam', name: 'exam.login')]
    public function index(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'token' => 'required',
        ]);

        $deliveryTaker = DB::table('delivery_taker')->where('token', $request->token)->first();

        if ($deliveryTaker) {
            $taker = Taker::query()->where('id', $deliveryTaker->taker_id)->first();
            $delivery = Delivery::query()->where('id', $deliveryTaker->delivery_id)->first();
            $attempt = $taker->attempts()->where('delivery_id', $deliveryTaker->delivery_id)->first();

            //            if (Delivery::STATUS_ON_PROGRESS !== $delivery->last_status) {
            //                return redirect()->back()->withErrors(['exam' => 'Exam is not active.']);
            //            }

            if ($deliveryTaker->is_login) {
                return redirect()->back()->withErrors(['exam' => 'Candidate is currently logged in']);
            }

            Session::put('exam', [
                'token' => $request->token,
                'taker' => $taker,
                'delivery' => $delivery,
                'admin' => null,
            ]);

            if (! is_null($attempt) && ! is_null($attempt->ended_at)) {
                return redirect()->route('exam.finished');
            }

            if (Delivery::STATUS_SCHEDULED === $delivery->last_status) {
                return redirect()->route('exam.waiting-room');
            }

            DB::table('delivery_taker')->where('token', $request->token)->update([
                'is_login' => true,
            ]);

            return redirect()->route('exam.main');
        }

        return redirect()->back()->withErrors(['token' => 'Token not found.']);
    }

    #[Get('/exam/logout', name: 'exam.logout')]
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $session = Session::get('exam');
        if (null != $session && array_key_exists('token', $session)) {
            DB::table('delivery_taker')->where('token', $session['token'])->update([
                'is_login' => false,
            ]);
        }
        Session::forget('exam');

        return redirect()->route('root');
    }
}
