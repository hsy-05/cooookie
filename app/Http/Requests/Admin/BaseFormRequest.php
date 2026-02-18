<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    /**
     * 取得系統統一的圖片驗證規則
     * 這樣以後你在 NewsRequest 或 CategoryRequest 只要呼叫這個方法即可
     */
    protected function getImageRules(): string
    {
        // 從我們存入 config 的 site_settings 中抓取，並設定預設值防呆
        $exts = config('site.image_extensions', 'jpg,jpeg,png,webp');
        $maxSize = config('site.image_max_size', 4096);

        // 回傳 Laravel 標準驗證格式
        return "nullable|image|mimes:{$exts}|max:{$maxSize}";
    }

    /**
     * 取得統一的錯誤訊息
     */
    protected function getImageMessages(): array
    {
        return [
            'image_url.image' => '上傳的檔案必須是圖片格式。',
            'image_url.mimes' => '僅支援：' . config('site.image_extensions', 'jpg,jpeg,png,webp') . ' 格式。',
            'image_url.max'   => '圖片太大了，上限為 ' . (config('site.image_max_size', 4096) / 1024) . 'MB。',
        ];
    }
}
