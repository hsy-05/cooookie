<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\ActionLog;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| php artisan inspire。這是一個內建的 Artisan 命令，會顯示一段勵志名言。
|--------------------------------------------------------------------------
*/
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('顯示勵志名言');


/*
|--------------------------------------------------------------------------
| 後台自動化排程 (Console Routes)
|--------------------------------------------------------------------------
| 這裡定義了專案中所有的背景任務與自動清理邏輯。
*/

/**
 * 自動清理操作紀錄
 * 規則：每天凌晨 3:00 執行，刪除 3 個月前的過期資料，維持資料庫效能。
 * 注意：這裡使用了 Laravel 的排程功能，確保你的伺服器有設定好排程任務 (cron job) 來執行 `php artisan schedule:run`。
 * 參考文件：https://laravel.com/docs/scheduling
 */
Schedule::call(function () {
    ActionLog::where('created_at', '<', Carbon::now()->subMonths(3))->delete();
})->dailyAt('03:00')->name('cleanup_old_action_logs');
