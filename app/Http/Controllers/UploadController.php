<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        Log::info('[uploadImage] 被呼叫了');
        if ($request->hasFile('image')) {
            // 儲存圖片
            $path = $request->file('image')->store('uploads', 'public');
            $fullUrl = asset('storage/' . $path);

            // 儲存時將網址轉成 [[SITE_URL]] 格式
            // $urlWithTag = ContentHelper::encodeSiteUrl($fullUrl);

            // 回傳給前端
            return response()->json(['url' => $fullUrl]);
        }

        return response()->json(['error' => '沒有檔案'], 400);
    }
}
