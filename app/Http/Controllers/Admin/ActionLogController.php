<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{ActionLog};
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth};
use App\Helpers\{ContentHelper};

class ActionLogController extends BaseAdminController
{
    // 定義這個 Controller 屬於哪組權限
    protected $permissionName = 'logs';
    protected $pageTitle = '操作日誌管理';

    public function index(Request $request)
    {
        // 自動清理舊資料 (簡單防呆)
        // ActionLog::where('created_at', '<', Carbon::now()->subMonths(3))->delete();

        // 處理日期快速篩選
        if ($request->has('quick_date')) {
            $date = match ($request->quick_date) {
                'week'      => Carbon::now()->subWeek(),
                'month'     => Carbon::now()->subMonth(),
                'half_year' => Carbon::now()->subMonths(6),
                'year'      => Carbon::now()->subYear(),
                default     => null,
            };
            if ($date) $request->merge(['start_date' => $date->toDateString()]);
        }

        $logs = ActionLog::with('user')
            ->filter($request->all())
            ->latest()
            ->paginate($request->input('per_page', 8));

        return view('admin.logs.index', compact('logs'));
    }

    /**
     * 單筆刪除 (修正 Method does not exist 錯誤)
     */
    public function destroy($id)
    {
        $log = ActionLog::findOrFail($id);
        $log->delete();

        // 雖然是刪除，還是要記一筆 Audit Log
        ActionLog::create([
            'user_id'    => Auth::id(),
            'action'     => '刪除',
            'log_info'   => "刪除單筆操作紀錄 ID: {$id}",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('form_success_swal', '該筆紀錄已成功刪除！');
    }

    /**
     * 批次刪除
     * 支援：
     *  A. 下拉選單依時間刪除
     *  B. 勾選多筆刪除
     */
    public function batchDestroy(Request $request)
    {
        $deletedCount = 0;
        $deleteType   = '';
        $deletedIds   = [];

        // A. 檢查是否有選擇「依時間刪除」
        if ($request->filled('delete_range')) {
            $date = match ($request->delete_range) {
                'week'      => Carbon::now()->subWeek(),
                'month'     => Carbon::now()->subMonth(),
                'half_year' => Carbon::now()->subMonths(6),
                'year'      => Carbon::now()->subYear(),
                default     => null,
            };

            if ($date) {
                // 先抓出要刪除的 ID
                $deletedIds = ActionLog::where('created_at', '<', $date)->pluck('id')->toArray();
                $deletedCount = count($deletedIds);

                // 刪除
                ActionLog::where('created_at', '<', $date)->delete();

                $deleteType = "清除 {$request->delete_range} 前";
            }
        }
        // B. 如果沒有選時間，則檢查 Checkbox (ids)
        elseif ($request->filled('ids') && is_array($request->ids)) {
            $deletedIds = $request->ids;
            $deletedCount = count($deletedIds);

            ActionLog::whereIn('id', $deletedIds)->delete();

            $deleteType = "手動勾選";
        }

        // C. 寫入操作紀錄
        if ($deletedCount > 0) {
            $this->writeBatchDeleteLog('操作日誌管理', $deletedCount, $deletedIds);

            return back()->with('form_success_swal', "成功刪除 {$deletedCount} 筆紀錄！");
        }

        return back()->with('form_error_swal', '未選擇任何資料或刪除範圍。');
    }
}
