<?php

namespace App\Http\Routes;

use Dentro\Yalr\BaseRoute;
use App\Http\Controllers\RootController;
use Illuminate\Support\Facades\Broadcast;

class RootRoute extends BaseRoute
{
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register(): void
    {
        $this->router->get('/', [
            'as' => 'root',
            'uses' => $this->uses('index'),
        ]);

        $this->router->get('/attachment/stream/{attachment_uuid}', [
            'as' => 'attachment.stream',
            'uses' => 'App\Http\Controllers\AttachmentController@stream',
        ]);

        Broadcast::routes();
    }

    public function controller(): string
    {
        return RootController::class;
    }
}
