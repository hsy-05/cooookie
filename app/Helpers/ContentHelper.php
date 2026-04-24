<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class ContentHelper
{
    /**
     * 儲存時：將完整 URL 換成 [[SITE_URL]] 標記
     *
     * @param string $content
     * @return string
     */
    public static function encodeSiteUrl(?string $content): string
    {

        // 空內容直接回空字串
        if (empty($content)) {
            return '';
        }

        Log::info('[encodeSiteUrl] 被呼叫了');
        $siteUrl = URL::to('/') . '/';
        return str_replace($siteUrl, '[[SITE_URL]]', $content);
    }

    /**
     * 顯示時：將 [[SITE_URL]] 標記還原成完整 URL
     *
     * @param string $content
     * @return string
     */
    public static function decodeSiteUrl(string $content)
    {
        $siteUrl = URL::to('/') . '/';
        return str_replace('[[SITE_URL]]', $siteUrl, $content);
    }

    /**
     * 根據系統設定過濾 HTML 內容
     * 用途：防止惡意指令碼 (XSS) 或不當標籤破壞網頁佈局
     * * @param string|null $content HTML 原始內容
     * @return string 清理後的安全內容
     */
    public static function cleanHtml(?string $content): string
    {
        // 防呆：內容為空就不用浪費效能跑過濾，直接回傳空字串
        if (empty($content)) {
            return '';
        }

        /**
         * 優先從資料庫設定抓取 (已透過 AppServiceProvider 注入 config('site'))
         * 若資料庫沒設，則預設過濾最危險的 script 和 iframe
         */
        $disallowed = config('site.editor_filter_tags', 'script,iframe');

        // 將逗號隔開的字串轉成陣列並去除多餘空白
        $tags = array_filter(array_map('trim', explode(',', $disallowed)));

        // 如果過濾清單真的是空的，就直接回傳內容
        if (empty($tags)) {
            return $content;
        }

        foreach ($tags as $tag) {
            // 專業正則處理：
            // 1. 處理成對標籤 (例如 <script>...</script>)，支援跨行偵測 (/s) 與不分大小寫 (/i)
            $fullTagPattern = '/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/is';
            $content = preg_replace($fullTagPattern, '', $content);

            // 2. 處理單一自閉合標籤 (例如 <iframe src="..." />)
            $singleTagPattern = '/<' . $tag . '\b[^>]*\/?>/is';
            $content = preg_replace($singleTagPattern, '', $content);
        }

        return $content;
    }
}
