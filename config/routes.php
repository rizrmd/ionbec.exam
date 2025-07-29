<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preloads
    |--------------------------------------------------------------------------
    | String of class name that instance of \Dentro\Yalr\Contracts\Bindable
    | Preloads will always been called even when laravel routes has been cached.
    | It is the best place to put Rate Limiter and route binding related code.
    */

    'preloads' => [
        App\Http\VariableBinder::class,
        App\Http\RateLimiter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Router group settings
    |--------------------------------------------------------------------------
    | Groups are used to organize and group your routes. Basically the same
    | group that used in common laravel route.
    |
    | 'group_name' => [
    |     // laravel group route options can contains 'middleware', 'prefix',
    |     // 'as', 'domain', 'namespace', 'where'
    | ]
    */

    'groups' => [
        'web' => [
            'middleware' => 'web',
            'prefix' => '',
        ],
        'back-office' => [
            'middleware' => ['web', 'auth'],
        ],
        'exam' => [
            'middleware' => 'exam',
            'prefix' => '',
        ],
        'taker' => [
            'middleware' => ['web', 'auth-taker'],
            'prefix' => 'taker',
        ],
        'api' => [
            'middleware' => 'api',
            'prefix' => 'api',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    | Below is where our route is loaded, it read `groups` section above.
    | keys in this array are the name of route group and values are string
    | class name either instance of \Dentro\Yalr\Contracts\Bindable or
    | controller that use attribute that inherit \Dentro\Yalr\RouteAttribute
    */

    'web' => [
        App\Http\Routes\RootRoute::class,
        App\Http\Controllers\Exam\ExamController::class,
        App\Http\Controllers\Taker\AuthController::class,
        App\Http\Controllers\Exam\FinishedController::class,
        \App\Http\Controllers\Interview\LiveController::class,
        /** @inject web **/
    ],
    'back-office' => [
        App\Http\Controllers\BackOffice\DashboardController::class,
        App\Http\Controllers\BackOffice\DeliveryController::class,
        App\Http\Controllers\BackOffice\TestController::class,
        App\Http\Controllers\BackOffice\GroupController::class,

        App\Http\Controllers\BackOffice\CategoryController::class,
        App\Http\Controllers\BackOffice\QuestionSetController::class,
        App\Http\Controllers\BackOffice\QuestionPackController::class,
        App\Http\Controllers\BackOffice\TestTakerController::class,

        App\Http\Controllers\BackOffice\ScoringController::class,
        App\Http\Controllers\BackOffice\ResultController::class,
        \App\Http\Controllers\BackOffice\LiveInterviewController::class,

        App\Http\Controllers\BackOffice\UserController::class,
        App\Http\Controllers\BackOffice\ProfileController::class,
        /** @inject back-office **/
    ],
    'exam' => [
        App\Http\Controllers\Exam\MainController::class,
        App\Http\Controllers\Exam\WaitingRoomController::class,
        /** @inject exam **/
    ],
    'taker' => [
        App\Http\Controllers\Taker\DashboardController::class,
        App\Http\Controllers\Taker\ScheduleController::class,
        App\Http\Controllers\Taker\ProfileController::class,
        /** @inject taker **/
    ],
    'api' => [
        App\Http\Controllers\Api\ZiggyController::class,
        /** @inject api **/
    ],
];
