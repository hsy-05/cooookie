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
     * 追蹤新上傳的暫存圖片 (加入時間戳記，防範重整誤殺)
     * 用於：處理「上傳了但最後沒按儲存」的垃圾檔案。
     *
     * @param string $path 圖片相對路徑
     * @param string $editorId 編輯器唯一編號
     */
    public static function trackTempImage(string $path, string $editorId = 'default'): void
    {
        // 專業防呆：不只存路徑，改存包含時間戳記的結構
        $tempData = [
            'path' => $path,
            'uploaded_at' => time() // 記錄目前時間（秒）
        ];

        // 將資料推入 Session 陣列中
        Session::push(self::SESSION_KEY . ".{$editorId}", $tempData);
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
 * 清理真正「已過期且失效」的暫存圖片
 * 用途：只清理超過指定時間（例如 1 小時）以上、無人認領的廢棄圖片，確保當前操作與剛重整的頁面絕對安全。
 */
public static function cleanAbandonedImages(): void
{
    // 抓出所有記錄在 Session 裡的暫存名單
    $allTemp = Session::get(self::SESSION_KEY, []);

    // 設定過期時間：1 小時前 (60分鐘 * 60秒)。你可以根據需求改成 24 小時 (24 * 3600)
    $expiryTime = time() - 3600;

    // 用來儲存「還沒過期、需要保留」的暫存名單，等一下要寫回 Session
    $keptTemp = [];

    foreach ($allTemp as $editorId => $items) {
        if (empty($items)) {
            continue;
        }

        foreach ($items as $item) {
            // 防呆防錯：確保資料結構正確（相容舊格式）
            $path = is_array($item) ? ($item['path'] ?? '') : $item;
            $uploadedAt = is_array($item) ? ($item['uploaded_at'] ?? 0) : 0;

            // 核心邏輯：如果沒有時間戳記（舊資料），或是上傳時間小於過期時間（代表它是很久以前留下的垃圾）
            if ($uploadedAt === 0 || $uploadedAt < $expiryTime) {
                if (!empty($path) && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                Log::info("系統大掃除：已自動清理超過 1 小時未儲存的廢棄圖片，來自編輯器 ID: {$editorId}, 路徑: {$path}");
            } else {
                // 時間還很新，代表可能是當前使用者正在編輯、或是剛重整頁面的圖，放回保留名單
                $keptTemp[$editorId][] = $item;
            }
        }
    }

    // 防呆關鍵：不再粗暴地用 Session::forget() 清空全站記錄
    if (!empty($keptTemp)) {
        // 如果還有需要保留的，把剩餘的乾淨資料寫回 Session
        Session::put(self::SESSION_KEY, $keptTemp);
    } else {
        // 全都過期清空了，才徹底移除 Key
        Session::forget(self::SESSION_KEY);
    }
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
