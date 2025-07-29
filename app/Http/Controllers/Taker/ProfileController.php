<?php

namespace App\Http\Controllers\Taker;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    #[Get('/profile', name: 'taker.profile.index')]
    public function index(): Response
    {
        return Inertia::render('Taker/Profile', [
            'taker' => auth()->guard('taker')->user(),
        ]);
    }

    #[Post('/profile', name: 'taker.profile.update-biodata')]
    public function updateBiodata(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['name' => 'required']);

        $taker = auth()->guard('taker')->user();
        $taker->name = $request->name;
        $taker->save();

        return $this->actionSuccess(message: 'Biodata updated successfully.');
    }

    #[Post('/profile/password', name: 'taker.profile.update-password')]
    public function updatePassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['password' => 'required|confirmed|min:8']);

        $taker = auth()->guard('taker')->user();
        $taker->password = Hash::make($request->password);
        $taker->save();

        auth()->guard('taker')->logout();

        return $this->actionSuccess(message: 'Password updated successfully.');
    }
}
