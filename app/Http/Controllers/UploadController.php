<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ImageHelper;
use App\Helpers\SummernoteImageHelper;
use App\Models\SystemSetting; // 引入模型讀取設定
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    /**
     * 處理 Summernote 編輯器上傳圖片
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        try {
            // 1. [防呆] 檢查檔案是否存在且有效
            if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
                return response()->json([
                    'success' => false,
                    'error'   => '檔案無效或未選擇檔案'
                ], 400);
            }

            $file = $request->file('image');

            // 2. [參數化] 從系統設定讀取目錄名稱，若無則預設為 'editor'
            $settings = SystemSetting::getAllSettings();
            $folder = $settings['editor_upload_dir'] ?? 'editor';

            // 3. [核心處理] 使用萬用 ImageHelper 上傳
            $path = ImageHelper::handleUpload($file, $folder);

            // 4. [追蹤機制] 只有在系統設定「開啟清理」時才記錄暫存名單
            $shouldCleanup = $settings['editor_auto_cleanup'] ?? '1';
            if ($shouldCleanup === '1') {
                SummernoteImageHelper::trackTempImage($path);
            }

            return response()->json([
                'success' => true,
                'url'     => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            Log::error("[Summernote 上傳錯誤] " . $e->getMessage());
            return response()->json(['success' => false, 'error' => '伺服器上傳失敗'], 500);
        }
    }

    /**
     * 獲取編輯器需要的系統設定參數
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEditorSettings()
    {
        // 假設你從資料庫抓取設定
        $settings = SystemSetting::pluck('setting_value', 'setting_key');

        return response()->json([
            'fonts' => $settings['editor_font_names'] ?? 'Arial,Microsoft JhengHei,Noto Sans TC',
            'sizes' => $settings['editor_font_sizes'] ?? '12,14,16,18,24,36',
            'css'   => isset($settings['editor_custom_css']) ? asset($settings['editor_custom_css']) : '',
        ]);
    }
}
