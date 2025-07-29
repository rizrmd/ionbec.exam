<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('scoring.{delivery_hash}', function ($user, $deliveryHash) {
    return true;
    //    return in_array("Administrator", $user->roles);
});

Broadcast::channel('live-interview.{delivery_hash}', function ($user) {
    return null !== $user;
});
