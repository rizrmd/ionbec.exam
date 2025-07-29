<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Accounts\User;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    #[Get('/back-office/profile', name: 'back-office.profile.index')]
    public function index(): Response
    {
        return Inertia::render('BackOffice/Profile/Index');
    }

    #[Post('/back-office/profile', name: 'back-office.profile.update-biodata')]
    public function updateBiodata(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validation = [
            'name' => 'required',
            'email' => 'required|email',
            'username' => 'required',
            'gender' => 'required|in:male,female,other',
            'birthday' => 'required|date_format:Y-m-d',
        ];

        $auth = auth()->user();
        if ($auth->email !== $request->email) {
            $validation['email'] = 'required|email|unique:users';
        }
        if ($auth->username !== $request->username) {
            $validation['username'] = 'required|unique:users';
        }
        $request->validate($validation);

        $user = User::query()->where('id', $auth->id)->first();
        $user->name = $request->name;
        if ($auth->email !== $request->email) {
            $user->email = $request->email;
        }
        if ($auth->username !== $request->username) {
            $user->username = $request->username;
        }
        $user->gender = $request->gender;
        $user->birthday = $request->birthday;
        $user->save();

        return $this->actionSuccess(message: 'Biodata updated successfully.');
    }

    #[Post('/back-office/profile/password', name: 'back-office.profile.update-password')]
    public function updatePassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['password' => 'required|confirmed|min:8']);

        $user = User::query()->where('id', auth()->user()->id)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return $this->actionSuccess(message: 'Password updated successfully.');
    }

    #[Post('/back-office/profile/image', name: 'back-office.profile.update-image')]
    public function updateImage(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['image' => 'required|mimes:jpeg,jpg,png|max:2048']);

        if ($request->file('image')) {
            $user = auth()->user();
            $file = $request->file('image');
            $fileName = $user->hash.'.jpg';

            $image = Image::make($file);
            $image->fit(250, 250, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image->encode('jpg');

            $storage = Storage::disk('public')->put('profile/'.$fileName, (string) $image->encode());

            $user->avatar = $fileName;
            $user->save();
        }

        return $this->actionSuccess(message: 'Image updated successfully.');
    }
}
