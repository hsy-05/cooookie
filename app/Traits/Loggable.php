<?php

namespace App\Traits;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Loggable
{
    /**
     * 寫入操作紀錄
     *
     * @param string $action 行為（新增 / 編輯 / 刪除）
     * @param string $title  顯示標題（由 Controller 決定）
     */
    public function writeLog(string $action, string $title = '')
    {
        if (!Auth::check() && $action === '登入') {
            return;
        }

        $modelName = $this->logName ?? class_basename($this);

        ActionLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'log_info'   => "{$action}{$modelName}: {$title}",
            'ip_address' => Request::ip(),
        ]);
    }
}
