<?php

namespace App\Helpers;

use Illuminate\Support\Facades\{Storage, Log, Session};

/**
 * Summernote 編輯器圖片同步工具
 * 整合了「內容比對法」與「Session 暫存追蹤機制」
 */
class SummernoteImageHelper
{
    // 定義 Session 中儲存暫存路徑的 Key
    private const SESSION_KEY = 'summernote_temp_uploads';

    /**
     * 同步編輯器內的圖片檔案 (比對新舊內容)
     * 用於：當使用者在編輯器「刪除」某張圖並「按下儲存」時，同步刪除實體檔案。
     *
     * @param string|null $oldContent 舊的 HTML 內容
     * @param string|null $newContent 新的 HTML 內容
     * @param string $disk 儲存磁碟預設為 public
     */
    public static function syncEditorImages(?string $oldContent, ?string $newContent, string $disk = 'public'): void
    {
        $oldImages = self::extractPaths($oldContent);
        $newImages = self::extractPaths($newContent);

        // 找出被刪除的圖 (舊的有，新的沒有)
        $deletedImages = array_diff($oldImages, $newImages);

        foreach ($deletedImages as $path) {
            $realPath = urldecode($path);
            if (Storage::disk($disk)->exists($realPath)) {
                Storage::disk($disk)->delete($realPath);
                // Log::info("Summernote 自動清理：使用者手動移除圖片 -> {$realPath}");
            }
        }
    }

    /**
     * 追蹤新上傳的暫存圖片 (還沒存檔前的觀察名單)
     * 用於：處理「上傳了但最後沒按儲存」的垃圾檔案。
     * * @param string $path 圖片相對路徑
     * @param string $editorId 編輯器唯一編號 (防止多分頁衝突)
     */
    public static function trackTempImage(string $path, string $editorId = 'default'): void
    {
        // 將路徑推入 Session 陣列中，以 editorId 作為區隔
        Session::push(self::SESSION_KEY . ".{$editorId}", $path);
    }

    /**
     * 存檔成功後，清空該次編輯器的追蹤記錄
     * 因為已經正式存入資料庫了，不需要再被「大掃除」掃掉。
     * * @param string $editorId 編輯器唯一編號
     */
    public static function commitTempImages(string $editorId = 'default'): void
    {
        Session::forget(self::SESSION_KEY . ".{$editorId}");
    }

    /**
     * 清理所有「已失效」的暫存圖片
     * 用於：當使用者進入新增/編輯頁面時，順手清理掉上一次「沒存檔就關掉」的廢棄圖。
     */
    public static function cleanAbandonedImages(): void
    {
        // 抓出所有記錄在 Session 裡的暫存名單
        $allTemp = Session::get(self::SESSION_KEY, []);

        foreach ($allTemp as $editorId => $paths) {
            // 如果這個 editorId 不是當前頁面正在用的，就視為廢棄
            // 實務上我們會直接清理掉「上一個 Session 週期」留下的所有東西
            if (!empty($paths)) {
                foreach ($paths as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
                Log::info("系統大掃除：已刪除未儲存的廢棄圖片，來自編輯器 ID: {$editorId}");
            }
        }

        // 掃完之後清空 Session 記錄
        Session::forget(self::SESSION_KEY);
    }

    /**
     * 從 HTML 中提取圖片路徑
     * * @param string|null $html HTML 內容
     * @return array 相對路徑陣列
     */
    private static function extractPaths(?string $html): array
    {
        if (empty($html)) return [];

        $paths = [];
        // 抓取包含 /storage/ 的路徑
        preg_match_all('/src=["\']([^"\']+\/storage\/([^"\']+))["\']/i', $html, $matches);

        if (!empty($matches[2])) {
            foreach ($matches[2] as $rawPath) {
                if (!str_contains($rawPath, 'data:image')) {
                    $paths[] = $rawPath;
                }
            }
        }
        return array_unique($paths);
    }
}
