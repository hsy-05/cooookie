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
        ActionLog::create([
            'user_id'    => $event->user->id,
            'action'     => '登入',
            'log_info'   => '管理者登入成功',
            'ip_address' => Request::ip(),
        ]);
    }
}
