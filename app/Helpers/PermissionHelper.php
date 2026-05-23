<?php

namespace App\Helpers;

/**
 * 後台權限結構輔助工具
 * 用途：統一集中處理 config/backend_permissions.php 的資料清洗、格式化與存在性檢查。
 */
class PermissionHelper
{
    /**
     * 靜態快取變數
     * 用途：在同一次網頁請求(Request)中，暫存已經解析過的權限地圖，避免重複跑迴圈浪費記憶體效能。
     */
    protected static ?array $cachedMap = null;

    /**
     * 取得全站註冊的合法權限清單字串陣列
     * 用途：產生一個純字串的一維陣列（例如 ['news.create', 'contact.edit']），供驗證入口快速比對。
     * @return array 一維字串陣列
     */
    public static function getValidPermissions(): array
    {
        // 優先讀取記憶體快取，若已有資料則不重複計算
        if (self::$cachedMap !== null) {
            return self::$cachedMap;
        }

        $rawConfig = config('backend_permissions') ?? [];
        $validPermissions = [];

        // 遍歷大分類
        foreach ($rawConfig as $group) {
            if (!isset($group['subs']) || !is_array($group['subs'])) {
                continue;
            }

            // 遍歷子功能模組
            foreach ($group['subs'] as $subKey => $sub) {
                if (!isset($sub['actions']) || !is_array($sub['actions'])) {
                    continue;
                }

                // 遍歷具體操作行為，將其組合成點記法格式
                foreach ($sub['actions'] as $actKey => $actLabel) {
                    $validPermissions[] = "{$subKey}.{$actKey}";
                }
            }
        }

        // 將結果寫入快取，供下一次呼叫直接使用
        self::$cachedMap = $validPermissions;

        return self::$cachedMap;
    }

    /**
     * 整理權限顯示結構（供後台管理員設定頁面渲染 checkbox 樹狀圖使用）
     * 用途：將設定檔的原始陣列加上 checked 狀態與 dependencies 完整名稱。
     * @param \App\Models\Admin $admin 當前正要被編輯的管理員 Eloquent 物件
     * @return array 格式化後的樹狀結構陣列
     */
    public static function preparePermissionsForForm($admin): array
    {
        $rawConfig = config('backend_permissions') ?? [];
        $processed = [];
        $userPermissions = $admin->permissions ?? [];

        foreach ($rawConfig as $groupKey => $group) {
            $subs = [];
            foreach ($group['subs'] as $subKey => $sub) {
                $actions = [];
                foreach ($sub['actions'] as $actKey => $actLabel) {
                    $permKey = "{$subKey}.{$actKey}";

                    // 解析權限相依性，將簡短名稱轉換為包含模組名前綴的完整名稱
                    $depends = [];
                    if (isset($sub['dependencies'][$actKey]) && is_array($sub['dependencies'][$actKey])) {
                        $depends = array_map(fn($d) => "{$subKey}.{$d}", $sub['dependencies'][$actKey]);
                    }

                    $actions[] = [
                        'key'     => $permKey,
                        'label'   => $actLabel,
                        'checked' => in_array($permKey, $userPermissions),
                        'depends' => $depends
                    ];
                }
                $subs[$subKey] = ['label' => $sub['label'], 'actions' => $actions];
            }
            $processed[$groupKey] = ['label' => $group['label'], 'subs' => $subs];
        }

        return $processed;
    }
}
