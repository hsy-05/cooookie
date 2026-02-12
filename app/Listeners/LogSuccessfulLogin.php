<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActionLog;
use App\Models\Admin;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\{Log};

class LogSuccessfulLogin
{
    /**
     * 當管理員登入時，紀錄操作紀錄
     * 透過 Session 標籤防止分頁重複讀取導致重複寫入
     */
    public function handle(Login $event): void
    {

        // Session 防重複寫入檢查
        if (Session::has('login_logged')) {
            return;
        }

        // 1. 防呆與身分判定：確保現在登入的人真的是我們定義的 Admin 模型
        // $event->user 雖然叫 user，但它裝的其實是你剛登入的 Admin 實例
        $admin = $event->user;

        // 檢查是否為 User 模型 (根據你的 Log，現在是 User)
        if (!$admin instanceof \App\Models\User) {
            Log::warning('身分判定失敗', ['class' => get_class($admin)]);
            return;
        }

        // 如果你只想紀錄「有權限進後台」的人，可以加一個簡單判斷
        // 例如檢查 role_id 或其他欄位
        if (empty($admin->role_id)) {
            return;
        }

        // ... 寫入資料庫
        \App\Models\ActionLog::create([
            'admin_id'   => $admin->id, // 這裡欄位名若叫 admin_id 沒關係，存的是 ID
            'action'     => '登入',
            'log_info'   => '管理者登入成功',
            'ip_address' => \Illuminate\Support\Facades\Request::ip(),
        ]);

        // 3. 在 Session 存入一個標籤，直到使用者登出或 Session 到期為止
        // 這樣同一個 Session 不管重新整理幾次，都不會再進到這裡寫入
        Session::put('login_logged', true);
    }
}
