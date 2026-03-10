<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ImageHelper;
use App\Helpers\SummernoteImageHelper;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\{Log, Storage};

/**
 * 統一檔案上傳控制器
 * 負責處理編輯器圖片上傳、系統設定獲取以及手動刪除請求。
 */
class UploadController extends Controller
{

    /**
     * 處理 Summernote 編輯器圖片 AJAX 上傳
     * * @param Request $request 包含 'image' 檔案的請求
     * @return \Illuminate\Http\JsonResponse 包含圖片網址或錯誤訊息
     */
    public function uploadEditorImage(Request $request)
    {
        try {
            // 【防呆】基本檔案檢查
            if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
                return response()->json([
                    'success' => false,
                    'error'   => '檔案無效或未選擇檔案'
                ], 400);
            }

            $file = $request->file('image');

            // 【參數化】讀取系統設定：決定存放目錄
            // 預設存放在 storage/app/public/editor
            $settings = SystemSetting::getAllSettings();
            $folder = $settings['editor_upload_dir'] ?? 'editor';

            // 執行上傳
            $path = ImageHelper::handleUpload($file, $folder, null, ['useOriginalName' => true]);

            // 【補強邏輯】將圖片路徑塞進暫存追蹤名單
            // 前端建議傳入一個隨機的 editor_id，如果沒有就用 default
            $editorId = $request->input('editor_id', 'default');

            // 只有在系統設定開啟清理時才記錄
            if (($settings['editor_auto_cleanup'] ?? '1') === '1') {
                SummernoteImageHelper::trackTempImage($path, $editorId);
            }

            // 【回傳結果】
            // 注意：我們不再呼叫 trackTempImage。
            // 圖片清理邏輯已全面移往資料儲存時的 HTML 比對機制。
            return response()->json([
                'success' => true,
                'url'     => asset('storage/' . $path)
            ]);

        } catch (\Exception $e) {
            Log::error("[Summernote 上傳錯誤] 發生於 UploadController: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => '伺服器儲存檔案失敗'
            ], 500);
        }
    }

    /**
     * 獲取編輯器前端初始化所需的動態參數 (字體、字級、CSS)
     * * @return \Illuminate\Http\JsonResponse
     */
    public function getEditorSettings()
    {
        // 讀取設定檔，提供預設值以防資料庫尚未設定
        $settings = SystemSetting::pluck('setting_value', 'setting_key');

        return response()->json([
            'fonts' => $settings['editor_font_names'] ?? 'Arial,Microsoft JhengHei,Noto Sans TC',
            'sizes' => $settings['editor_font_sizes'] ?? '12,14,16,18,24,36,48',
            'css'   => isset($settings['editor_custom_css']) ? asset($settings['editor_custom_css']) : '',
        ]);
    }

    /**
     * 手動觸發：刪除伺服器上的編輯器圖片
     * 用於 Summernote 的 onMediaDelete 事件，實現即時清理。
     * * @param Request $request 包含 'image_url' 的請求
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEditorImage(Request $request)
    {
        $imageUrl = $request->input('image_url');

        if (empty($imageUrl)) {
            return response()->json(['status' => 'error', 'message' => '缺少圖片網址'], 400);
        }

        // 【安全性】檢查是否為本站 storage 檔案，防止惡意刪除外部連結或系統檔案
        if (!str_contains($imageUrl, '/storage/')) {
            return response()->json(['status' => 'error', 'message' => '非本站儲存路徑，拒絕操作'], 403);
        }

        // 【路徑解析】從 URL 提取相對路徑 (例如從 http://domain.com/storage/news/1.jpg 提取出 news/1.jpg)
        $relativePath = urldecode(last(explode('/storage/', $imageUrl)));

        // 【安全性】防範路徑穿越 (Directory Traversal)
        if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
            return response()->json(['status' => 'error', 'message' => '路徑格式非法'], 403);
        }

        // 【執行刪除】
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
            Log::info("Summernote 手動刪除成功: {$relativePath}");
            return response()->json(['status' => 'success', 'message' => '檔案已同步移除']);
        }

        return response()->json(['status' => 'error', 'message' => '檔案不存在或已在存檔時被清理'], 404);
    }
}
