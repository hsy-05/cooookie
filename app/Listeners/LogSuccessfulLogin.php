<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * 處理管理員登入成功後的紀錄行為
     * (利用 Session 標籤防止重複寫入，避免 F5 重新整理產生多筆垃圾紀錄)
     *
     * @param Login $event Laravel 內建的登入事件物件
     * @return void
     */
    public function handle(Login $event): void
    {
        // 1. 防重複機制：這回合 Session 已經記過登入了，就直接收工
        if (Session::has('login_logged')) {
            return;
        }

        $user = $event->user;

        // 2. 身份防呆：確保觸發登入的是我們的「後台管理員/使用者」模型
        // (避免前台一般會員登入時，也跑來寫後台的 ActionLog 導致報錯)
        if (!$user instanceof \App\Models\User) {
            Log::warning('登入紀錄失敗：未知的 User 模型類別', ['class' => get_class($user)]);
            return;
        }

        // 3. 權限防呆：只有具備 role_id (代表是後台人員) 才需要被記錄
        if (empty($user->role_id)) {
            return;
        }

        // 4. 正式寫入資料庫
        ActionLog::create([
            'admin_id'   => $user->id,
            'action'     => '登入',
            'log_info'   => '管理者登入成功',
            'ip_address' => Request::ip() ?? '127.0.0.1',
        ]);

        // 5. 貼上 Session 護身符，直到使用者登出前，都不會再重複記錄
        Session::put('login_logged', true);
    }
}
