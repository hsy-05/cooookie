<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class LogSuccessfulLogin
{
    /**
     * 當管理員登入時，紀錄操作紀錄
     * 透過 Session 標籤防止分頁重複讀取導致重複寫入
     */
    public function handle(Login $event): void
    {
        // 1. 檢查 Session 中是否已經有「已紀錄登入」的標籤
        // 如果已經有了，代表這次 Session 期間已經寫過 Log，直接結束程式
        if (Session::has('login_logged')) {
            return;
        }

        // 2. 寫入資料庫
        ActionLog::create([
            'user_id'    => $event->user->id,
            'action'     => '登入',
            'log_info'   => '管理者登入成功',
            'ip_address' => Request::ip(),
        ]);

        // 3. 在 Session 存入一個標籤，直到使用者登出或 Session 到期為止
        // 這樣同一個 Session 不管重新整理幾次，都不會再進到這裡寫入
        Session::put('login_logged', true);
    }
}
