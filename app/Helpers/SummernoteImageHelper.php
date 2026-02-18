<?php

namespace App\Helpers;

use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SummernoteImageHelper
{
    /**
     * 【防呆機制】暫存區的 Session Key
     */
    private const TEMP_SESSION_KEY = 'summernote_temp_images';

    /**
     * 清理 Summernote 內容中被刪除的圖片檔案。
     * 比較舊內容和新內容中的圖片 URL，如果舊的有、新的沒有，就代表被使用者刪掉了。
     *
     * @param string $oldContent  舊內容（資料庫撈出來的原始資料）
     * @param string $newContent  新內容（使用者剛送出的資料）
     * @param string $imageSubDir 圖片儲存的子目錄名稱（例如 'news'）
     * @param string $logContext  Log 標籤，方便日後查修（例如 '最新消息'）
     * @return void
     */
    public static function cleanupSummernoteImages(string $oldContent, string $newContent, string $imageSubDir, string $logContext = 'Summernote'): void
    {
        // 防呆：如果前後內容都是空的，直接結束，節省效能
        if (empty($oldContent) && empty($newContent)) {
            return;
        }

        // 1. 解碼內容，確保比對的網址格式一致 (把 [[SITE_URL]] 換成真實網址)
        $decodedOldContent = ContentHelper::decodeSiteUrl($oldContent);
        $decodedNewContent = ContentHelper::decodeSiteUrl($newContent);

        // 2. 抓出新舊內容裡的所有圖片網址
        $oldUrls = self::extractImagePaths($decodedOldContent, $imageSubDir);
        $newUrls = self::extractImagePaths($decodedNewContent, $imageSubDir);

        // 3. 陣列比對：舊的有，但新的沒有，就是要刪除的目標
        $deletedPaths = array_diff($oldUrls, $newUrls);

        if (empty($deletedPaths)) {
            return; // 沒圖要刪就收工
        }

        // 4. 執行刪除 (利用我們寫好的 ImageHelper，它支援陣列批次刪除)
        ImageHelper::deleteImage($deletedPaths, 'public');

        Log::info("[{$logContext}] 編輯器圖片清理完成，共刪除 " . count($deletedPaths) . " 張廢棄圖片。");
    }

    /**
     * 從 HTML 內容中提取所有屬於我們系統的圖片「相對路徑」。
     *
     * @param string $content     HTML 內容
     * @param string $imageSubDir 圖片儲存的子目錄（例如 'news'）
     * @return array 圖片相對路徑陣列 (例如 ['news/123.jpg', 'news/456.png'])
     */
    public static function extractImagePaths(string $content, string $imageSubDir): array
    {
        if (empty($content)) return [];

        // 使用正則表達式抓取 img 標籤中的 src 屬性
        $pattern = '/<img[^>]+src=["\']([^"\']+)["\']/i';
        preg_match_all($pattern, $content, $matches);

        $paths = [];
        if (!empty($matches[1])) {
            foreach (array_unique($matches[1]) as $url) {
                // 防呆：只處理我們自己 storage 裡的圖片，忽略外部圖片 (例如 imgur 網址)
                if (str_contains($url, "/storage/{$imageSubDir}/")) {
                    // 把完整 URL 截斷，只保留相對路徑 (例如 news/xxx.jpg)
                    $parts = explode("/storage/", $url);
                    if (isset($parts[1])) {
                        $paths[] = $parts[1];
                    }
                }
            }
        }

        return $paths;
    }

    /* =========================================================
       以下是新增的「未儲存廢棄圖片」追蹤機制 (Session 方案)
       ========================================================= */

    /**
     * [步驟 1] 上傳時記錄：將 AJAX 剛上傳的圖片路徑記錄到 Session 中
     *
     * @param string $path 圖片相對路徑
     * @return void
     */
    public static function trackTempImage(string $path): void
    {
        Session::push(self::TEMP_SESSION_KEY, $path);
    }

    /**
     * [步驟 2] 成功儲存時：清空 Session，代表圖片已正式被採用，不需要刪除
     *
     * @return void
     */
    public static function commitTempImages(): void
    {
        Session::forget(self::TEMP_SESSION_KEY);
    }

    /**
     * [步驟 3] 頁面載入時大掃除：刪除上次上傳到一半卻沒儲存的廢棄圖片
     *
     * @return void
     */
    public static function cleanAbandonedImages(): void
    {
        $abandonedImages = Session::get(self::TEMP_SESSION_KEY, []);

        if (!empty($abandonedImages)) {
            // 刪除實體檔案
            ImageHelper::deleteImage($abandonedImages, 'public');
            Log::info("系統大掃除：已刪除 " . count($abandonedImages) . " 張未儲存的 Summernote 廢棄圖片。");

            // 清空 Session
            Session::forget(self::TEMP_SESSION_KEY);
        }
    }
}
