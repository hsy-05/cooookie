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

        /**
         * 檢查配置：如果目前讀取的欄位有列在 Model 的 $tagFields 陣列中
         * 則代表該欄位需要從「字串」轉回「陣列」顯示
         */
        if (isset($this->tagFields) && in_array($key, $this->tagFields)) {
            // 透過 Helper 執行轉換邏輯
            return TagHelper::toArray($value);
        }

        // 若不是標籤欄位，則依照原本的邏輯回傳
        return $value;
    }
}
