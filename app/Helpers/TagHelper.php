<?php

namespace App\Helpers;

/**
 * 專業標籤格式轉換工具
 * 負責將前端複雜的輸入（陣列、JSON 或髒字串）標準化為資料庫儲存格式，或反向還原
 */
class TagHelper
{
    /**
     * 將各類輸入值轉為「純文字逗號隔開」的字串
     * 用於資料庫存檔，確保中文不被編碼，維持原始文字可讀性
     *
     * @param mixed $value 前端傳入值 (支援陣列、JSON 字串或一般字串)
     * @return string|null
     */
    public static function toString($value): ?string
    {
        // 若輸入為空（null, 空陣列, 空字串），統一回傳 null 存入資料庫
        if (empty($value)) {
            return null;
        }

        // 自動解析：若傳入的是 JSON 字串，先解碼回陣列以便後續清洗
        if (is_string($value) && str_starts_with($value, '[')) {
            $value = json_decode($value, true);
        }

        // 格式化處理：若為陣列，執行標準清洗流程
        if (is_array($value)) {
            // 依序執行：去前後空白 -> 過濾掉長度為 0 的空項目 -> 移除重複標籤
            $cleanArray = array_unique(array_filter(array_map('trim', $value), 'strlen'));

            // 使用 implode 結合，這能確保資料庫中儲存的是漂亮的「標籤1,標籤2」
            return implode(',', $cleanArray);
        }

        // 若本來就是一般字串，簡單去除首尾空格後直接回傳
        return trim((string)$value);
    }

    /**
     * 將資料庫儲存的逗號字串還原為前端可用的「陣列」
     *
     * @param mixed $value 資料庫讀出的原始內容
     * @return array
     */
    public static function toArray($value): array
    {
        // 沒資料就給空陣列，確保前端 foreach 時不會噴錯
        if (empty($value)) {
            return [];
        }

        // 如果已經是陣列（例如其他邏輯已處理過），直接回傳
        if (is_array($value)) {
            return $value;
        }

        // 拆解字串並清洗，確保陣列中的每個項目都是乾淨的純文字
        return array_filter(array_map('trim', explode(',', $value)), 'strlen');
    }
}
