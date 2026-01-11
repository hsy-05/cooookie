<?php

namespace App\Traits;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Loggable
{
    // 當 Model 啟動時執行
    public static function bootLoggable()
    {
        // 監聽：新增後
        static::created(function ($model) {
            self::logAction('新增', $model);
        });

        // 監聽：更新後
        static::updated(function ($model) {
            self::logAction('編輯', $model);
        });

        // 監聽：刪除後
        static::deleted(function ($model) {
            self::logAction('刪除', $model);
        });
    }

    // 實際寫入資料庫的函式
    protected static function logAction($action, $model)
    {
        // 如果不是登入狀態 (例如系統排程執行)，就不紀錄，避免報錯
        if (!Auth::check()) return;

        // --- 重點：處理標題 ---
        // 1. 先看 Model 有沒有設定 $logTitle 指定欄位
        // 2. 再看 Model 有沒有寫 getLogTitleAttribute() 方法 (處理多語系用)
        // 3. 真的都沒有，就抓 id
        $title = 'ID: ' . $model->id;

        if (isset($model->logTitle) && !empty($model->{$model->logTitle})) {
            $title = $model->{$model->logTitle};
        }

        // 取得該 Model 的中文名稱 (例如：消息、產品)
        $modelName = $model->logName ?? class_basename($model);

        ActionLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'log_info'   => "{$action}{$modelName}: {$title}",
            'ip_address' => Request::ip(),
        ]);
    }
}
