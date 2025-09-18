<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function telegramCheckStatus()
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    public function telegramConnect()

    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
