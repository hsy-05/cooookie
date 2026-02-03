<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    // 當登入發生時，Laravel 會把 Login $event 傳進來
    public function handle(Login $event): void
    {
        ActionLog::firstOrCreate([
            'user_id'    => $event->user->id,
            'action'     => '登入',
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ], [
            'log_info' => '管理者登入成功',
        ]);
    }
}
