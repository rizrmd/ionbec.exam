<?php

namespace App\Http\Controllers\Api;

use Tightenco\Ziggy\Ziggy;
use Dentro\Yalr\Attributes\Get;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ZiggyController extends Controller
{
    #[Get('ziggy', name: 'api.ziggy')]
    public function index(): JsonResponse
    {
        return response()->json(new Ziggy());
    }
}
