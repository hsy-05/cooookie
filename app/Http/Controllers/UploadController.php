<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\Log;
use App\Helpers\ImageHelper;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        Log::info('[uploadImage] 被呼叫了');

        if ($request->hasFile('image')) {
            $saveDir = 'uploads'; // 儲存子目錄
            $file = $request->file('image');

            // 生成唯一檔名（已先檢查是否存在）
            $filename = ImageHelper::generateUniqueFilename($file);
            $fullPath = $saveDir . '/' . $filename;

            // 使用指定檔名儲存圖片
            $file->storeAs($saveDir, $filename, 'public');

            // 產生圖片的完整網址
            $fullUrl = asset('storage/' . $fullPath);

            // 儲存時將網址轉成 [[SITE_URL]] 格式
            // $urlWithTag = ContentHelper::encodeSiteUrl($fullUrl);

            // 回傳給前端
            return response()->json([
                'success' => true,
                'url' => $fullUrl
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => '沒有檔案'
        ], 400);
    }
}
