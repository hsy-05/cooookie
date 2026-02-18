<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ImageHelper;
use App\Helpers\SummernoteImageHelper;
use Illuminate\Support\Facades\Log;

/**
 * 檔案上傳專用控制器
 * 專門處理來自前端編輯器（如 Summernote）或其他 AJAX 的檔案上傳需求
 */
class UploadController extends Controller
{
    /**
     * 處理 Summernote 編輯器上傳圖片的請求
     * * @param  Request $request 前端傳過來的請求，包含 image 檔案欄位
     * @return \Illuminate\Http\JsonResponse 回傳 JSON 給前端編輯器以插入圖片
     */
    public function uploadImage(Request $request)
    {
        try {
            // 1. [防呆] 檢查請求中是否真的有檔案
            if (!$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'error'   => '請選擇要上傳的圖片檔案'
                ], 400);
            }

            $file = $request->file('image');

            // 2. [防呆] 檢查檔案是否有效（例如是否超過 PHP 限制或損毀）
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'error'   => '檔案無效或上傳失敗'
                ], 400);
            }

            /**
             * 3. [核心處理] 執行上傳
             * 使用我們優化過的 ImageHelper，這會自動處理檔名安全性。
             * 這裡我們統一將編輯器圖片存在 'editor' 目錄下。
             */
            $path = ImageHelper::handleUpload($file, 'editor');

            /**
             * 4. [防遺留機制] 記錄到 Session 觀察名單
             * 當圖片上傳成功，但使用者「還沒按儲存表單」前，這張圖都算「暫存」。
             * 我們呼叫先前寫好的 SummernoteImageHelper 幫我們盯著它。
             */
            SummernoteImageHelper::trackTempImage($path);

            // 5. 組合完整網址並回傳
            $fullUrl = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'url'     => $fullUrl
            ]);

        } catch (\Exception $e) {
            // 發生未預期錯誤時，紀錄 Log 方便後續追查
            Log::error("[Summernote 上傳錯誤] " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => '系統發生錯誤，請稍後再試'
            ], 500);
        }
    }
}
