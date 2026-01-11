<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActionLogController extends Controller
{
    public function index(Request $request)
    {
        // 1. 自動刪除一個月前的紀錄 (防呆機制)
        // 每次進來這個頁面順便掃地，刪除 1 個月前的資料
        ActionLog::where('created_at', '<', Carbon::now()->subMonth())->delete();

        // 2. 處理「快速時間」下拉選單邏輯
        if ($request->has('quick_date')) {
            $date = match($request->quick_date) {
                'week'      => Carbon::now()->subWeek(),
                'month'     => Carbon::now()->subMonth(),
                'half_year' => Carbon::now()->subMonths(6),
                'year'      => Carbon::now()->subYear(),
                default     => null,
            };
            if ($date) {
                $request->merge(['start_date' => $date->toDateString()]);
            }
        }

        // 3. 撈資料 + 分頁
        $logs = ActionLog::with('user') // 預先載入 User，效能優化
            ->filter($request->all())   // 使用 Model 裡的 scopeFilter
            ->latest()                  // 依照時間新->舊排序
            ->paginate($request->input('per_page', 20));

        return view('admin.logs.index', compact('logs'));
    }
}
