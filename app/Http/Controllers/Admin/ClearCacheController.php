<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\JsonResponse;

class ClearCacheController extends Controller
{
    /**
     * 執行清除快取指令
     */
    public function clearCache(): JsonResponse
    {
        try {
            // 清除應用程式快取
            Artisan::call('cache:clear');
            // 清除路由快取
            Artisan::call('route:clear');
            // 清除設定快取
            Artisan::call('config:clear');
            // 清除編譯過的 Blade 視圖
            Artisan::call('view:clear');

            return response()->json(['status' => 'success', 'message' => '系統快取已清除！']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => '清除失敗：' . $e->getMessage()], 500);
        }
    }
}
