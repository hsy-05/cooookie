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
     *
     * @param Login $event Laravel 內建的登入事件
     * @return void
     */
    public function handle(Login $event): void
    {
        // 取得當前嘗試登入的使用者物件
        $user = $event->user;

        // 防呆邏輯：確保有拿到使用者物件，且擁有管理者權限 (role_id 不為空)
        // 這樣可以避免一般會員（未來若有）的登入也被寫進管理員日誌
        if (!$user || empty($user->role_id)) {
            return;
        }

        /**
         * 專業防呆：原子鎖 (Atomic Lock)
         * 理由：Session 寫入速度有時跟不上毫秒級的重複觸發。
         * 我們建立一個「登入鎖」，有效期限設為 5 秒，這 5 秒內同一個人的登入動作只會被記錄一次。
         */
        $lockKey = 'login_lock_' . $user->id;

        // 嘗試取得鎖，如果這把鎖已經存在，代表剛才已經記錄過了，直接結束
        if (!Cache::add($lockKey, true, 5)) {
            return;
        }

        // 執行資料庫寫入動作
        $this->saveLoginLog($user);
    }

    /**
     * 實際執行資料庫寫入
     * 用途：將登入資訊結構化後存入 admin_logs 資料表
     *
     * @param \App\Models\User $user 登入的使用者物件
     * @return void
     */
    private function saveLoginLog($user): void
    {
        try {
            // 使用 Eloquent 模型建立紀錄，確保自動填入 created_at
            ActionLog::create([
                'admin_id'   => $user->id,
                'action'     => '登入',
                'log_info'   => '管理者登入成功123',
                'ip_address' => Request::ip() ?? '127.0.0.1', // 防呆：取不到 IP 時給預設值
                // 專業小技巧：面試官很愛看這個，紀錄設備資訊有助於安全性追蹤
                'user_agent' => substr(Request::userAgent(), 0, 255),
            ]);
        } catch (\Exception $e) {
            // 系統防呆：日誌紀錄不能影響主程式運作
            // 如果寫入日誌失敗（例如資料庫欄位長度不夠），僅記錄錯誤訊息到 Laravel Log，不跳出報錯頁面
            Log::error("管理者登入日誌寫入失敗，錯誤原因: " . $e->getMessage());
        }
    }
}
