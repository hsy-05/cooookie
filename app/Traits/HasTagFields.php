<?php

namespace App\Traits;

use App\Helpers\TagHelper;

/**
 * 標籤欄位自動轉換特徵
 * 讓 Model 具備「自動化讀取」能力：當程式讀取特定標籤欄位時，自動將資料庫字串轉回陣列格式
 */
trait HasTagFields
{
    /**
     * 攔截 Laravel Model 的屬性取得機制
     * 當你在外部呼叫 $item->meta_keyword 時，會自動進入此處判斷
     *
     * @param string $key 欄位名稱
     * @return mixed
     */
    public function getAttribute($key)
    {
        // 先取得資料庫最原始的屬性內容
        $value = parent::getAttribute($key);

        // 判斷邏輯：
        // 1. 使用 property_exists 檢查物件是否有定義該屬性（不論 protected 或 private）
        // 2. 確保該屬性是陣列，避免 in_array 報錯
        if (property_exists($this, 'tagFields') && is_array($this->tagFields)) {
            if (in_array($key, $this->tagFields)) {
                return TagHelper::toArray($value);
            }
        }

        // 若不是標籤欄位，則依照原本的邏輯回傳
        return $value;
    }
}
