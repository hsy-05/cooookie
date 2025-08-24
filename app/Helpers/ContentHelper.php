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
}
