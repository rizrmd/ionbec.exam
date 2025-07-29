<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Accounts\Role;
use App\Models\Accounts\User;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Delete;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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

    #[Get('/back-office/user', name: 'back-office.user.index')]
    public function index(Request $request): Response
    {
        $userQuery = User::query();

        $count = $userQuery->clone()->count();

        $userQuery->when($request->input('role') ?? false, function ($query, $role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('slug', $role);
            });
        });

        $userQuery->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                $q->where('email', 'like', "%{$queryString}%");
                $q->orWhere('name', 'like', "%{$queryString}%");
            });
        });

        return Inertia::render('BackOffice/User/Index', [
            'roles' => Role::all(),
            'count' => $count,
            'payload' => $userQuery->whereNot('id', auth()->user()->id)->with('roles')->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }

    #[Post('/back-office/user', name: 'back-office.user.store')]
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:255',
            'roles' => 'required|array',
            'gender' => 'required|string|in:male,female',
        ]);

        $roles = Role::whereIn('slug', $request->roles)->pluck('id');

        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->gender = $request->gender;
        $user->save();

        $user->roles()->attach($roles);

        return $this->actionSuccess();
    }

    #[Put('/back-office/user/{user_hash}', name: 'back-office.user.update')]
    public function update(Request $request, User $user)
    {
        $validation = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255'.(($user->username !== $request->username) ? '|unique:users' : null),
            'email' => 'required|email|max:255'.(($user->email !== $request->email) ? '|unique:users' : null),
            'roles' => 'required|array',
            'gender' => 'required|string|in:male,female',
        ];
        $request->validate($validation);

        $roles = Role::whereIn('slug', $request->roles)->pluck('id');

        $user->name = $request->name;
        $user->gender = $request->gender;
        if ($user->username !== $request->username) {
            $user->username = $request->username;
        }
        if ($user->email !== $request->email) {
            $user->email = $request->email;
        }
        $user->save();

        $user->roles()->sync($roles);

        return $this->actionSuccess();
    }

    #[Delete('/back-office/user/{user_hash}', name: 'back-office.user.destroy')]
    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->delete();

        return $this->actionSuccess(message: 'User deleted successfully.');
    }

    #[Post('/back-office/user/{user_hash}/change-password', name: 'back-office.user.change-password')]
    public function changePassword(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|max:255|confirmed',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->actionSuccess();
    }
}
