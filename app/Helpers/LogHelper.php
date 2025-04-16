<?php

namespace App\Helpers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogHelper
{
    public static function save($action, $message = null)
    {
        Log::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'action' => $action,
            'message' => $message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
