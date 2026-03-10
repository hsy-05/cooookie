<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * 處理管理員登入成功後的紀錄行為
     * * @param Login $event Laravel 內建的登入事件
     * @return void
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // 防呆邏輯：確保有拿到使用者物件，且是管理者 (role_id 有值)
        if (!$user || empty($user->role_id)) {
            return;
        }

        $sessionKey = 'login_logged_' . $user->id;

        if (session()->has($sessionKey)) {
            // 如果已經標記過，代表是重複觸發，直接回傳不執行後續動作
            return;
        }

        // 執行資料庫寫入
        $this->saveLoginLog($user);

        // 【關鍵步驟】寫入完畢後，在 Session 中蓋章，標記為 true
        // 這樣在同一個連線週期內，這段程式就不會再跑第二次
        session()->put($sessionKey, true);
    }

    /**
     * 實際執行資料庫寫入
     * 將資料寫入 admin_logs (ActionLog 模型)
     * * @param \App\Models\User $user 登入的使用者物件
     * @return void
     */
    private function saveLoginLog($user): void
    {
        try {
            ActionLog::create([
                'admin_id'   => $user->id,
                'action'     => '登入',
                'log_info'   => '管理者登入成功',
                'ip_address' => Request::ip() ?? '127.0.0.1',
                // 如果需要更細節，可以記錄 User Agent
                // 'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // 避免因為紀錄失敗導致使用者無法登入，僅做 Log 提醒
            Log::error("登入日誌寫入失敗: " . $e->getMessage());
        }
    }
}
