<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Delete;
use Illuminate\Support\Facades\DB;
use App\Models\Takers\RegisterData;
use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordTestTaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Veelasky\LaravelHashId\Rules\ExistsByHash;
use App\Jobs\Takers\Verification as TakerVerification;

class TestTakerController extends Controller
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

    #[Get('back-office/test-taker', name: 'back-office.test-taker.index')]
    public function index(Request $request): Response
    {
        $takerQuery = Taker::query();

        $count = $takerQuery->clone()->count();

        $countVerified = $takerQuery
            ->clone()
            ->where('is_verified', true)
            ->count();

        $countNonVerified = $takerQuery
            ->clone()
            ->where('is_verified', false)
            ->count();

        $takerQuery->when($request->input('group') ?? false, function ($query, $group) {
            $query->whereHas('groups', function ($q) use ($group) {
                $id = Group::hashToId($group);
                $q->where('id', $id);
            });
        });

        $takerQuery->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                $q->where('email', 'like', "%{$queryString}%");
                $q->orWhere('name', 'like', "%{$queryString}%");
            });
        });

        $takerQuery->when($request->input('date') ?? false, function ($query, $date) {
            $query->whereDate('created_at', $date);
        });

        $takerQuery->when($request->input('verified') ?? false, function ($query, $verified) {
            $query->where('is_verified', 'true' === $verified);
        });

        return Inertia::render('BackOffice/TestTaker/Index', [
            'groups' => Group::query()->latest()->get(),
            'count' => $count,
            'countVerified' => $countVerified,
            'countNonVerified' => $countNonVerified,
            'payload' => $takerQuery->with(['groups', 'registerData.group', 'registerData.delivery'])->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }

    #[Post('/back-office/test-taker', name: 'back-office.test-taker.store')]
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:takers',
            'password' => 'max:255|min:8',
            'groups' => 'array',
        ]);

        $groupIds = [];
        foreach ($request->groups as $hash) {
            $groupIds[] = Group::hashToId($hash);
        }

        $taker = new Taker();
        $taker->reg = $request->reg;
        $taker->name = $request->name;
        $taker->email = $request->email;
        $taker->password = $request->password;
        $taker->is_verified = true;
        $taker->save();

        $taker->groups()->attach($groupIds);

        DB::transaction(function () use ($taker, $request) {
            $taker->groups()->select(['id'])->cursor()
                ->each(fn ($group) => $taker->groups()
                    ->wherePivot('taker_code', null)
                    ->updateExistingPivot($group->id, ['taker_code' => $request->reg]));
        });

        return $this->actionSuccess();
    }

    #[Put('/back-office/test-taker/{taker_hash}', name: 'back-office.test-taker.update')]
    public function update(Request $request, Taker $taker): \Illuminate\Http\RedirectResponse
    {
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|email'.(($taker->email !== $request->email) ? '|unique:takers' : null),
            'groups' => 'array',
        ];
        $request->validate($validation);

        $groupIds = [];
        foreach ($request->groups as $hash) {
            $groupIds[] = Group::hashToId($hash);
        }

        $taker->reg = $request->reg;
        $taker->name = $request->name;
        if ($taker->email !== $request->email) {
            $taker->email = $request->email;
        }
        $taker->save();

        $taker->groups()->sync($groupIds);

        DB::transaction(function () use ($taker, $request) {
            $taker->groups()->select(['id'])->cursor()
                ->each(fn ($group) => $taker->groups()
                    ->wherePivot('taker_code', null)
                    ->updateExistingPivot($group->id, ['taker_code' => $request->reg]));
        });

        return $this->actionSuccess();
    }

    #[Delete('/back-office/test-taker/{taker_hash}', name: 'back-office.test-taker.destroy')]
    public function destroy(Taker $taker): \Illuminate\Http\RedirectResponse
    {
        $taker->delete();

        return $this->actionSuccess(message: 'Candidate deleted successfully.');
    }

    #[Post('/back-office/test-taker/generate-password', name: 'back-office.test-taker.generate-password')]
    public function generatePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['hash' => ['required', new ExistsByHash(Taker::class)]]);

        $taker = Taker::byHash($request->hash);
        $newPassword = $this->generateRandomString();
        $taker->password = Hash::make($newPassword);
        $taker->save();

        return response()->json([
            'taker' => $taker,
            'password' => $newPassword,
        ]);
    }

    public function generateRandomString($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    #[Post('/back-office/test-taker/sent-password', name: 'back-office.test-taker.sent-password')]
    public function sentPasswordToEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['hash' => ['required', new ExistsByHash(Taker::class)]]);

        $taker = Taker::byHash($request->hash);
        $newPassword = $this->generateRandomString();
        $taker->password = Hash::make($newPassword);
        $taker->save();

        $mailData = [
            'name' => $taker->name,
            'email' => $taker->email,
            'new-password' => $newPassword,
        ];

        Mail::to($taker->email)->send(new ResetPasswordTestTaker($mailData));

        return response()->json([
            'taker' => $taker,
        ]);
    }

    #[Post('/back-office/test-taker/{taker_hash}/verification', name: 'back-office.test-taker.verification')]
    public function verification(Request $request, Taker $taker): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'register_data_hash' => ['nullable', new ExistsByHash(RegisterData::class)],
            'taker_code' => ['nullable'],
        ]);

        $registrationData = ! is_null($request->register_data_hash) ? RegisterData::byHash($request->register_data_hash) : null;
        $this->dispatchSync(new TakerVerification($registrationData, $taker));

        return $this->actionSuccess(message: 'Candidate Verified successfully.');
    }
}
