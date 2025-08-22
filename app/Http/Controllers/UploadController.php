<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ContentHelper;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            // 儲存圖片
            $path = $request->file('image')->store('uploads', 'public');
            $fullUrl = asset('storage/' . $path);

            // 儲存時將網址轉成 [[SITE_URL]] 格式
            $urlWithTag = ContentHelper::encodeSiteUrl($fullUrl);

            // 回傳給前端
            return response()->json(['url' => $urlWithTag]);
        }

        return response()->json(['error' => '沒有檔案'], 400);
    }
}
