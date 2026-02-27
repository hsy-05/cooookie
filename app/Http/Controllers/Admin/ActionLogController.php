<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ActionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, Log};
use App\Helpers\ContentHelper;

/**
 * 操作紀錄管理
 * 負責全站管理員行為審計紀錄 (Audit Logs) 的查詢與維護。
 */
class ActionLogController extends BaseAdminController
{
    // 定義權限模組名稱
    protected $permissionName = 'logs';
    protected $pageTitle = '操作紀錄';

    /**
     * 列表頁：顯示操作紀錄與篩選
     */
    public function index(Request $request)
    {
        // 1. 處理日期快速篩選 (將語意化的時間轉為實際日期)
        if ($request->filled('quick_date')) {
            $startDate = $this->parseQuickDate($request->quick_date);
            if ($startDate) {
                // 將計算好的日期併入 Request，讓 Model 的 filter 能接手處理
                $request->merge(['start_date' => $startDate->toDateString()]);
            }
        }

        // 2. 抓取資料 (預先載入管理員關聯，避免迴圈內重複查詢資料庫)
        $logs = ActionLog::with('admin')
            ->filter($request->all())
            ->latest()
            ->paginate($request->input('per_page', 20)); // 紀錄通常較多，預設給 20 筆

        return $this->view('admin.logs.index', compact('logs'));
    }

    /**
     * 單筆刪除
     * 雖然通常紀錄不建議刪除，但因應需求提供權限管控下的清理
     */
    public function destroy($id)
    {
        try {
            $log = ActionLog::findOrFail($id);
            $logInfo = "[{$log->action}] {$log->model_name} - {$log->log_info}";

            $log->delete();

            // 紀錄是誰刪除了紀錄 (Audit the Auditors)
            $this->writeLog('刪除', "刪除單筆操作紀錄：{$logInfo}", 'ActionLog', $id);

            return back()->with('form_success_swal', '該筆紀錄已成功移除');
        } catch (\Exception $e) {
            Log::error("ActionLog Destroy Error: " . $e->getMessage());
            return back()->with('form_error_swal', '刪除失敗，系統發生錯誤');
        }
    }

    /**
     * 批次處理：支援「勾選刪除」與「時間區間清理」
     */
    public function batchDestroy(Request $request)
    {
        $deletedCount = 0;
        $deletedIds = [];

        // 邏輯 A：檢查是否有勾選特定的 ID
        if ($request->filled('ids') && is_array($request->ids)) {
            $deletedIds = $request->ids;
            $deletedCount = ActionLog::whereIn('id', $deletedIds)->delete();
            $summary = "手動勾選刪除 {$deletedCount} 筆資料";
        }
        // 邏輯 B：依據時間範圍進行大範圍清理 (常用於資料庫瘦身)
        elseif ($request->filled('delete_range')) {
            $limitDate = $this->parseQuickDate($request->delete_range);

            if ($limitDate) {
                // 先計算數量才刪除，為了寫 Log 交代清楚
                $deletedCount = ActionLog::where('created_at', '<', $limitDate)->count();
                ActionLog::where('created_at', '<', $limitDate)->delete();
                $summary = "清理 {$request->delete_range} 之前的舊紀錄 (共 {$deletedCount} 筆)";
            }
        }

        // 執行日誌與回傳
        if ($deletedCount > 0) {
            $this->writeLog('批次刪除', $summary, 'ActionLog');
            return back()->with('form_success_swal', "操作完成，已移除 {$deletedCount} 筆紀錄");
        }

        return back()->with('form_error_swal', '請選擇要刪除的項目或範圍');
    }

    /* --- 內部輔助方法 --- */

    /**
     * 語意化日期轉換器
     * 將前台下拉選單的字串轉為 Carbon 物件
     * * @param string $type 時間類型 (week, month...)
     * @return Carbon|null
     */
    private function parseQuickDate(string $type)
    {
        return match ($type) {
            'week'      => Carbon::now()->subWeek(),
            'month'     => Carbon::now()->subMonth(),
            'half_year' => Carbon::now()->subMonths(6),
            'year'      => Carbon::now()->subYear(),
            'two_years' => Carbon::now()->subYears(2),
            default     => null,
        };
    }

    /**
     * 統一紀錄日誌的方法 (覆寫或直接呼叫)
     * 確保即使在 ActionLogController 內也能正確存檔
     */
    protected function writeLog($action, $info, $model = null, $id = null)
    {
        ActionLog::create([
            'admin_id'   => Auth::id(),
            'action'     => $action,
            'log_info'   => $info,
            'model_name' => $model,
            'model_id'   => $id,
            'ip_address' => request()->ip(),
        ]);
    }
}
