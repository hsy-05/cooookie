<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    /**
     * 取得系統統一的圖片驗證規則
     * * @return string Laravel 驗證規則字串
     */
    protected function getImageRules(): string
    {
        // 從系統參數抓取，並提供專業防呆預設值
        $exts = config('site.image_extensions', 'jpg,jpeg,png,webp');
        $maxSize = config('site.image_max_size', 4096);

        // nullable: 代表非必填
        // image: 確保檔案是真的圖片檔
        // mimes: 限制副檔名
        // max: 限制檔案大小 (單位是 KB)
        return "nullable|image|mimes:{$exts}|max:{$maxSize}";
    }

    /**
     * 取得系統統一的一般檔案驗證規則 (例如 PDF, Word 等)
     * * @return string Laravel 驗證規則字串
     */
    protected function getFileRules(): string
    {
        // 抓取後台「上傳設定」中的檔案副檔名與大小限制
        $exts = config('site.file_extensions', 'pdf,doc,docx,zip');
        $maxSize = config('site.file_max_size', 10240); // 預設 10MB

        return "nullable|file|mimes:{$exts}|max:{$maxSize}";
    }

    /**
     * 取得統一的圖片錯誤訊息
     * * @param string $attribute 欄位名稱 (預設為 image_url)
     * @return array 訊息陣列
     */
    protected function getImageMessages(string $attribute = 'image_url'): array
    {
        $mb = config('site.image_max_size', 4096) / 1024;

        return [
            "{$attribute}.image" => '上傳的檔案必須是圖片格式。',
            "{$attribute}.mimes" => '圖片格式僅支援：' . config('site.image_extensions', 'jpg,jpeg,png,webp') . '。',
            "{$attribute}.max"   => "圖片太大了，上限為 {$mb}MB。",
        ];
    }

    /**
     * 取得統一的檔案錯誤訊息
     * * @param string $attribute 欄位名稱
     * @return array 訊息陣列
     */
    protected function getFileMessages(string $attribute = 'file_url'): array
    {
        $mb = config('site.file_max_size', 10240) / 1024;

        return [
            "{$attribute}.file"  => '上傳的內容必須是一個有效的檔案。',
            "{$attribute}.mimes" => '檔案格式僅支援：' . config('site.file_extensions', 'pdf,doc,docx,zip') . '。',
            "{$attribute}.max"   => "檔案太大了，上限為 {$mb}MB。",
        ];
    }
}
