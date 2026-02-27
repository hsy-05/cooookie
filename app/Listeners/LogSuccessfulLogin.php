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

        // 1. 基礎防呆：確保使用者物件存在，且具備管理者權限 (role_id)
        if (!$user || empty($user->role_id)) {
            return;
        }

        /**
         * 2. 原子鎖機制 (核心解決方案)
         * * 用途：利用快取系統建立一個「瞬時鎖」，防止併發請求導致重複寫入。
         * 鎖的名稱：根據使用者 ID 定義，確保不會擋到別人。
         * 鎖的時間：設定 10 秒，這足以應付任何瞬間噴發的重複事件。
         */
        $lockKey = 'login_log_lock_' . $user->id;

        // 嘗試獲取鎖，如果這把鎖已經被別人拿走了，就直接結束
        // 如果沒人拿，我們就拿走並執行閉包內的程式碼
        Cache::lock($lockKey, 10)->get(function () use ($user) {

            // 3. 執行正式寫入
            $this->saveLoginLog($user);

            // 這裡不需要手動釋放鎖，10秒後會自動過期
            // 這樣可以保證這 10 秒內該帳號不會再產生第二筆「登入成功」紀錄
        });
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
