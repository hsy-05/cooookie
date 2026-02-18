<?php

namespace App\Traits;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Loggable
{
    /**
     * 寫入系統操作紀錄
     *
     * @param string $action 行為名稱（例如：新增、編輯、刪除、匯出）
     * @param string $title  操作對象的標題或識別名稱
     * @return void
     */
    public function writeLog(string $action, string $title = ''): void
    {
        // 1. 防呆：確保在「非網頁環境」(例如 CLI 終端機或背景排程) 執行時不會報錯
        if (app()->runningInConsole()) {
            return; // 背景排程不需要寫入管理員操作紀錄
        }

        // 2. 判斷操作者 ID (若未登入則記為 0，代表系統或訪客)
        $adminId = Auth::id() ?? 0;

        // 3. 特殊例外：如果是登入動作，且尚未有 Auth session，交由 Listener 處理就好，這裡跳過
        if ($adminId === 0 && $action === '登入') {
            return;
        }

        // 4. 決定模組名稱 (優先抓取 Model 裡自訂的 $logName，若無則抓 Model 類別名稱)
        $modelName = $this->logName ?? class_basename($this);

        // 5. 寫入資料庫
        ActionLog::create([
            'admin_id'   => $adminId,
            'action'     => $action,
            'log_info'   => "{$action} {$modelName} : {$title}", // 加上空格讓閱讀更順暢
            'ip_address' => Request::ip() ?? '127.0.0.1', // 防呆：確保 IP 不會是 null
        ]);
    }
}
