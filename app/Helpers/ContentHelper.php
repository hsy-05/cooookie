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
     * 顯示提示訊息
     ** @param int    $msgType      消息類型: 0=消息, 1=錯誤, 2=詢問
     * @param string $msgContent   訊息內容
     * @param array  $links        連結選項 [['text' => '...', 'href' => '...']]
     * @param bool   $autoRedirect 是否自動跳轉
     */
    public static function showMsg(int $msgType = 0, string $msgContent = '', array $links = [], bool $autoRedirect = true)
    {
        // 如果沒有提供任何連結，預設給予「返回上一頁」
        if (empty($links)) {
            $links[] = [
                'text' => '返回上一頁',
                'href' => 'javascript:history.go(-1);'
            ];
        } else {
            // 依照鍵名進行排序 (數值越負，代表排序順序越後面)
            $links = array_values($links);
        }

        // 對應你的 admin.page-message 元件所需的格式
        session()->flash('form_success', [
            'msg_type'     => $msgType,
            'title'        => $msgContent,
            'links'        => $links,
            'autoRedirect' => $autoRedirect,
        ]);
    }

    /**
     * 根據系統設定過濾 HTML 內容
     * @param string $content 原始內容
     * @return string 安全的內容
     */
    public static function cleanHtml(string $content): string
    {
        $disallowed = config('site.editor_filter_tags', 'script,iframe');
        $tags = explode(',', $disallowed);

        foreach ($tags as $tag) {
            $tag = trim($tag);
            // 使用正則移除特定標籤及其內容
            $content = preg_replace('/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/is', "", $content);
        }

        return $content;
    }
}
