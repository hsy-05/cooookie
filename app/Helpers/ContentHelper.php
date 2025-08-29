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
    public static function encodeSiteUrl(string $content)
    {
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
     *
     * @param integer    $msgType        消息類型，0消息，1錯誤，2詢問
     * @param string     $msgContent     訊息內容
     * @param array      $links          連結選項
     * @param bool       $autoRedirect   是否自動跳轉（預設 true，跳轉到第一個連結）
     */
    public static function showMsg(int $msgType = 0, string $msgContent, array $links = [], bool $autoRedirect = true)
    {
        if (($linkNum = count($links)) == 0) {
            $links[0]['text'] = '返回上一頁';
            $links[0]['href'] = 'javascript:history.go(-1);';
        }

        if ($linkNum > 1) {
            // 依照鍵名進行排序 (數值越負，代表排序順序越後面)
            uksort(
                $links,
                function ($a, $b) use ($linkNum) {
                    $a < 0 && $a = $linkNum + abs($a);
                    $b < 0 && $b = $linkNum + abs($b);

                    return ($b < $a) ? 1 : -1;
                }
            );
        }

        session()->flash('form_success', [
            'msg_type'     => $msgType,
            'title'        => $msgContent,
            'links'        => $links,
            'autoRedirect' => $autoRedirect,
        ]);
    }
}
