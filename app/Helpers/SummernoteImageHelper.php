<?php

namespace App\Helpers;

use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\Log;

class SummernoteImageHelper
{
    /**
     * 清理 Summernote 內容中被刪除的圖片檔案（靜態方法）。
     * 比較舊內容和新內容中的圖片 URL，刪除舊內容中有但新內容中沒有的圖片檔案。
     *
     * @param string $oldContent 舊內容（從資料庫載入，可能已編碼）
     * @param string $newContent 新內容（從請求中編碼後）
     * @param string $imageSubDir 圖片儲存的子目錄，例如 'uploads' 或 'news/content'
     * @param string $logContext 記錄日誌時的上下文，例如 'News' 或 'Product'
     * @return void
     */
    public static function cleanupSummernoteImages(string $oldContent, string $newContent, string $imageSubDir, string $logContext = 'Summernote')
    {
        // 如果沒有舊內容或新內容，則無需清理
        if (empty($oldContent) && empty($newContent)) {
            return;
        }

        // 1. 解碼舊內容和新內容，確保圖片 URL 格式一致
        $decodedOldContent = ContentHelper::decodeSiteUrl($oldContent);
        $decodedNewContent = ContentHelper::decodeSiteUrl($newContent);

        // 2. 從內容中提取圖片 URL
        $oldUrls = self::extractSummernoteImageUrls($decodedOldContent, $imageSubDir);
        $newUrls = self::extractSummernoteImageUrls($decodedNewContent, $imageSubDir);

        // 3. 找出被刪除的 URL（舊的有，新中沒有）
        $deletedUrls = array_diff($oldUrls, $newUrls);

        if (empty($deletedUrls)) {
            Log::debug("{$logContext} Summernote 圖片清理：沒有圖片被刪除。");
            return;
        }

        Log::info("{$logContext} Summernote 圖片清理：發現 " . count($deletedUrls) . " 張圖片被刪除。");

        // 4. 準備要刪除的相對檔案路徑
        $deletedFilePaths = [];
        foreach ($deletedUrls as $url) {
            $pattern = '/^\/storage\/' . preg_quote($imageSubDir, '/') . '\//';
            if (preg_match($pattern, $url)) {
                $filePath = str_replace('/storage/', '', $url);
                $deletedFilePaths[] = $filePath;
            } else {
                Log::warning("{$logContext} Summernote 圖片清理：發現非預期格式的圖片 URL，跳過刪除：{$url}");
            }
        }

        if (empty($deletedFilePaths)) {
            Log::debug("{$logContext} Summernote 圖片清理：沒有符合條件的檔案路徑可供刪除。");
            return;
        }

        // 5. 使用 ImageHelper 刪除檔案
        $results = ImageHelper::deleteImage($deletedFilePaths, 'public');

        // 6. 記錄刪除結果
        foreach ($deletedFilePaths as $path) {
            $success = is_array($results) ? ($results[$path] ?? false) : $results;
            if ($success) {
                Log::info("{$logContext} Summernote 圖片清理：成功刪除檔案：{$path}");
            } else {
                Log::error("{$logContext} Summernote 圖片清理：刪除檔案失敗或檔案不存在：{$path}");
            }
        }
    }

    /**
     * 從 HTML 內容中提取所有 <img src="..."> 的 URL（靜態方法）。
     * 只提取以 /storage/{$imageSubDir}/ 開頭的圖片 URL。
     *
     * @param string $content HTML 內容
     * @param string $imageSubDir 圖片儲存的子目錄，例如 'uploads' 或 'news/content'
     * @return array 圖片 URL 陣列
     */
    public static function extractSummernoteImageUrls(string $content, string $imageSubDir): array
    {
        $urls = [];
        if (empty($content)) {
            return $urls;
        }

        $pattern = '/<img[^>]+src=["\']([^"\']*\/storage\/' . preg_quote($imageSubDir, '/') . '\/[^"\']+)["\'][^>]*>/i';
        preg_match_all($pattern, $content, $matches);

        if (isset($matches[1])) {
            $urls = array_unique($matches[1]); // 去重
        }

        return $urls;
    }
}
