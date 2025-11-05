<?php

namespace App\Http\Controllers;

use App\Models\Takers\Taker;
use App\Models\Deliveries\Delivery;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PublicTokenLoginController extends Controller
{
    /**
     * Display token login form
     */
    #[Get('/exam-login', name: 'exam.login.form')]
    public function showForm(): Response
    {
        return Inertia::render('Public/ExamLogin');
    }

    /**
     * Handle token login
     */
    #[Get('/exam/{token}', name: 'exam.token.login')]
    public function tokenLogin(string $token)
    {
        Log::info('PublicTokenLogin: Attempting login with token', ['token' => $token]);

        // Find delivery_taker record with this token
        $deliveryTaker = \DB::table('delivery_taker')
            ->where('token', strtoupper($token))
            ->first();

        if (!$deliveryTaker) {
            Log::warning('PublicTokenLogin: Token not found', ['token' => $token]);
            return redirect()->route('exam.login.form')
                ->with('error', 'Invalid token. Please check your token and try again.');
        }

        // Get delivery details
        $delivery = Delivery::find($deliveryTaker->delivery_id);
        if (!$delivery) {
            Log::error('PublicTokenLogin: Delivery not found', ['delivery_id' => $deliveryTaker->delivery_id]);
            return redirect()->route('exam.login.form')
                ->with('error', 'Delivery not found. Please contact administrator.');
        }

        // Get taker details
        $taker = Taker::find($deliveryTaker->taker_id);
        if (!$taker) {
            Log::error('PublicTokenLogin: Taker not found', ['taker_id' => $deliveryTaker->taker_id]);
            return redirect()->route('exam.login.form')
                ->with('error', 'Taker not found. Please contact administrator.');
        }

        // Check if delivery is still active (not expired)
        if ($delivery->ended_at && now()->isAfter($delivery->ended_at)) {
            Log::warning('PublicTokenLogin: Delivery expired', [
                'delivery_id' => $delivery->id,
                'token' => $token,
                'ended_at' => $delivery->ended_at
            ]);
            return redirect()->route('exam.login.form')
                ->with('error', 'This exam has expired. The exam ended at ' . $delivery->ended_at->format('Y-m-d H:i:s'));
        }

        // Check if already logged in
        if ($deliveryTaker->is_login) {
            Log::info('PublicTokenLogin: Already logged in', [
                'taker_id' => $taker->id,
                'delivery_id' => $delivery->id,
                'token' => $token
            ]);
        } else {
            // Mark as logged in
            \DB::table('delivery_taker')
                ->where('delivery_id', $delivery->delivery_id)
                ->where('taker_id', $delivery->taker_id)
                ->update(['is_login' => true]);

            Log::info('PublicTokenLogin: Marked as logged in', [
                'taker_id' => $taker->id,
                'delivery_id' => $delivery->id,
                'token' => $token
            ]);
        }

        // Clear any existing exam session
        Session::forget('exam');

        // Set exam session data
        Session::put('exam', [
            'token' => $token,
            'taker' => $taker,
            'delivery' => $delivery,
            'admin' => null,
        ]);

        Log::info('PublicTokenLogin: Session created', [
            'taker_id' => $taker->id,
            'taker_name' => $taker->name,
            'delivery_id' => $delivery->id,
            'delivery_name' => $delivery->name,
            'token' => $token,
            'scheduled_at' => $delivery->scheduled_at,
            'automatic_start' => $delivery->automatic_start
        ]);

        // Check if should redirect to waiting room or directly to exam
        if ($delivery->automatic_start && strtotime($delivery->scheduled_at) > strtotime('now')) {
            Log::info('PublicTokenLogin: Redirecting to waiting room', [
                'scheduled_at' => $delivery->scheduled_at,
                'current_time' => now(),
                'time_diff_minutes' => (strtotime($delivery->scheduled_at) - strtotime('now')) / 60
            ]);
            return redirect()->route('exam.waiting-room');
        }

        Log::info('PublicTokenLogin: Redirecting to exam directly');
        return redirect('/exam');
    }

    /**
     * Handle POST token login from form
     */
    #[Post('/exam-login', name: 'exam.login.submit')]
    public function tokenLoginPost(Request $request)
    {
        $request->validate([
            'token' => 'required|string|min:3|max:10'
        ]);

        $token = strtoupper(trim($request->token));

        Log::info('PublicTokenLogin: Form submission', ['token' => $token]);

        return redirect()->route('exam.token.login', ['token' => $token]);
    }

    /**
     * Handle logout
     */
    #[Get('/exam-logout', name: 'exam.logout')]
    public function logout(Request $request)
    {
        $sessionData = Session::get('exam');

        if ($sessionData && isset($sessionData['token'])) {
            // Mark as logged out
            $token = $sessionData['token'];
            \DB::table('delivery_taker')
                ->where('token', $token)
                ->update(['is_login' => false]);

            Log::info('PublicTokenLogin: Logged out', ['token' => $token]);
        }

        Session::forget('exam');

        return redirect()->route('exam.login.form')
            ->with('success', 'You have been logged out successfully.');
    }
}