<?php

namespace App\Http\Controllers\Taker;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\Group\AddTestTakerToGroup;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class AuthController extends Controller
{
    #[Get('/taker-register', name: 'taker.register')]
    public function register(): Response
    {
        return Inertia::render('Taker/Register');
    }

    #[Post('/taker-register', name: 'taker.sign-up')]
    public function signUp(Request $request)
    {
        $validation = [
            'name' => 'required',
            'email' => 'required|unique:takers',
            'password' => 'required|confirmed|min:8',
            'group' => '',
        ];

        if (! is_null($request->group)) {
            $validation['group'] = ['required', new ExistsByHash(Group::class)];
        }

        $request->validate($validation);

        $taker = new Taker();
        $taker->name = $request->name;
        $taker->email = $request->email;
        $taker->password = bcrypt($request->password);
        $taker->save();

        if (! is_null($request->group)) {
            dispatch_sync(new AddTestTakerToGroup($taker, Group::byHash($request->group)));
        }

        return $this->actionSuccess('/', 'Registration succeed! Your account is under verification, please wait 24 hours to sign-in. If you unable to sign-in within 24 hours, please contact the administrator.');
    }

    #[Post('/taker-register/delivery', name: 'taker.register.delivery')]
    public function getDelivery(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'date' => 'date_format:Y-m-d',
        ]);

        $today = Carbon::now();

        if (Carbon::createFromFormat('Y-m-d', $request->date)->gte($today) || $today->format('Y-m-d') === $request->date) {
            $deliveries = Delivery::query()
                ->where('last_status', Delivery::STATUS_SCHEDULED)
                ->whereDate('scheduled_at', $request->date)
                ->get();

            return response()->json($deliveries);
        }

        return response()->json([]);
    }

    #[Post('/taker-register/groups', name: 'taker.register.groups')]
    public function getGroups(Request $request): \Illuminate\Http\JsonResponse
    {
        $groups = Group::query()
            ->whereDate('closed_at', '>', Carbon::now())
            ->get();

        return response()->json($groups);
    }

    #[Get('/taker-login', name: 'taker.login')]
    public function login(): Response
    {
        $client = request()->attributes->get('client');
        
        return Inertia::render('Taker/Login', [
            'client' => $client ? [
                'name' => $client->name,
                'logo_url' => $client->logo_url,
            ] : null,
        ]);
    }

    #[Post('/taker-login', name: 'taker.sign-in')]
    public function signIn(Request $request): \Illuminate\Http\RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'login_id' => ['nullable', 'uuid'],
        ]);

        $clientId = app()->has('current_client')
            ? app('current_client')->id
            : $request->session()->get('client_id');
        abort_unless($clientId, 403, 'Tenant context required.');

        // Email is a contact address. A shared address must never select the
        // first account or use a password to guess which identity was intended.
        $query = Taker::query()->where('client_id', $clientId)
            ->where('email', $credentials['email']);
        if (! empty($credentials['login_id'])) {
            $query->where('login_id', $credentials['login_id']);
        }
        $ids = (clone $query)->limit(2)->pluck('id');
        if ($ids->count() !== 1) {
            return back()->withErrors([
                'email' => 'Check your credentials. If this email is shared, enter your participant ID.',
            ])->onlyInput('email', 'login_id');
        }

        $login = [
            'id' => $ids->first(),
            'client_id' => $clientId,
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ];
        if (Auth::guard('taker')->attempt($login)) {
            $request->session()->regenerate();
            $taker = Auth::guard('taker')->user();
            if (! $taker->is_verified) {
                Auth::guard('taker')->logout();
                return back()->withErrors([
                    'email' => 'Taker not verified! please contact admin.',
                ])->onlyInput('email', 'login_id');
            }
            return redirect()->route('taker.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email', 'login_id');
    }
}
