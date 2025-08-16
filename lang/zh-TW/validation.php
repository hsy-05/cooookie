<?php

return [

    /*
|----------------------------------------------------------------------------------------
| 驗證語言行
|----------------------------------------------------------------------------------------
|
| 以下語言行包含驗證器類別所使用的預設錯誤訊息。
| 其中一些規則有多個版本，例如
| 大小規則。您可以隨意在此處調整這些訊息。
|
*/

    'accepted' => '必須接受 :attribute 欄位。 ',
    'accepted_if' => '當 :other 為 :value 時，必須接受 :attribute 欄位。 ',
    'active_url' => ':attribute 欄位必須是有效的 URL。 ',
    'after' => ':attribute 欄位必須是 :date 之後的日期。 ',
    'after_or_equal' => ':attribute 欄位必須是 :date 之後或等於 :date 的日期。 ',
    'alpha' => ':attribute 欄位只能包含字母。 ',
    'alpha_dash' => ':attribute 欄位只能包含字母、數字、短劃線和底線。 ',
    'alpha_num' => ':attribute 欄位只能包含字母和數字。 ',
    'any_of' => ':attribute 欄位無效。 ',
    'array' => ':attribute 欄位必須是陣列。 ',
    'ascii' => ':attribute 欄位只能包含單字節字母數字字元和符號。 ',
    'before' => ':attribute 欄位必須是早於 :date 的日期。 ',
    'before_or_equal' => ':attribute 欄位必須是早於或等於 :date 的日期。 ',
    'between' => [
        'array' => ':attribute 欄位必須包含 :min 到 :max 個項目。 ',
        'file' => ':attribute 欄位必須介於 :min 和 :max 千位元組之間。 ',
        'numeric' => ':attribute 欄位必須介於 :min 和 :max 之間。 ',
        'string' => ':attribute 欄位必須介於 :min 和 :max 之間字元。 ',
    ],
    'boolean' => ':attribute 欄位必須為 true 或 false。 ',
    'can' => ':attribute 欄位包含未授權的值。 ',
    'confirmed' => ':attribute 欄位確認不符。 ',
    'contains' => ':attribute 欄位缺少必要值。 ',
    'current_password' => '密碼不正確。 ',
    'date' => ':attribute 欄位必須是有效日期。 ',
    'date_equals' => ':attribute 欄位必須是等於 :date 的日期。 ',
    'date_format' => ':attribute 欄位必須符合 :format 格式。 ',
    'decimal' => ':attribute 欄位必須包含 :decimal 小數位。 ',
    'declined' => ' :attribute 欄位必須被拒絕。 ',
    'declined_if' => '當 :other 為 :value 時，:attribute 欄位必須被拒絕。 ',
    'different' => ':attribute 欄位和 :other 必須不同。 ',
    'digits' => ':attribute 欄位必須是 :digits 位元。 ',
    'digits_between' => ':attribute 欄位必須介於 :min 和 :max 位元之間。 ',
    'dimensions' => ':attribute 欄位的圖片尺寸無效。 ',
    'distinct' => ':attribute 欄位有重複的值。 ',
    'doesnt_end_with' => ':attribute 欄位不能以下列值結尾：:values。 ',
    'doesnt_start_with' => ':attribute 欄位不能以下列值開頭以下：:values。 ',
    'email' => ':attribute 欄位必須是有效的電子郵件地址。 ',
    'ends_with' => ':attribute 欄位必須以以下之一結尾：:values。 ',
    'enum' => '所選的 :attribute 無效。 ',
    'exists' => '所選的 :attribute 無效。 ',
    'extensions' => ':attribute 欄位必須具有以下副檔名之一：:values。 ',
    'file' => ':attribute 欄位必須是檔案。 ',
    'filled' => ':attribute 欄位必須有一個值。 ',
    'gt' => [
        'array' => ':attribute 欄位必須包含多於 :value 項目。 ',
        'file' => ':attribute 欄位必須大於 :value千位元組。 ',
        'numeric' => ':attribute 欄位必須大於 :value。 ',
        'string' => ':attribute 欄位必須大於 :value 個字元。 ',
    ],
    'gte' => [
        'array' => ':attribute 欄位必須包含 :value 個或更多項。 ',
        'file' => ':attribute 欄位必須大於或等於 :value 個千位元組。 ',
        'numeric' => ':attribute 欄位必須大於或等於 :value 個字元。 ',
        'string' => ':attribute 欄位必須大於或等於 :value 個字元。 ',
    ],
    'hex_color' => ':attribute 欄位必須是有效的十六進位顏色值。 ',
    'image' => ':attribute 欄位必須是圖像。 ',
    'in' => '所選的 :attribute 無效。 ',
    'in_array' => ':attribute 欄位必須存在於 :other 中。 ',
    'in_array_keys' => ' :attribute 欄位必須至少包含以下鍵之一：:values。 ',
    'integer' => ':attribute 欄位必須是整數。 ',
    'ip' => ':attribute 欄位必須是有效的 IP 位址。 ',
    'ipv4' => ':attribute 欄位必須是有效的 IPv4 位址。 ',
    'ipv6' => ':attribute 欄位必須是有效的 IPv6 位址。 ',
    'json' => ':attribute 欄位必須是有效的 JSON 字串。 ',
    'list' => ':attribute 欄位必須是列表。 ',
    'lowercase' => ':attribute 欄位必須為小寫。 ',
    'lt' => [
        'array' => ':attribute 欄位必須少於 :value 個項目。 ',
        'file' => ':attribute 欄位必須少於 :value 個千位元組。 ',
        'numeric' => ':attribute 欄位必須少於 :value 個字元。 ',
        'string' => ':attribute 欄位必須少於 :value 個字元。 ',
    ],
    'lte' => [
        'array' => ':attribute 欄位不得超過 :value 個項目。 ',
        'file' => ':attribute 欄位必須小於或等於 :value 個千位元組。 ',
        'numeric' => ':attribute 欄位必須小於或等於 :value 個字元。 ',
        'string' => ':attribute 欄位必須小於或等於 :value 個字元。 ',
    ],
    'mac_address' => ':attribute 欄位必須是有效的 MAC 位址。 ',
    'max' => [
        'array' => ':attribute 欄位的項目數不得超過 :max 個。 ',
        'file' => ':attribute 欄位的長度不得超過 :max 個千位元組。 ',
        'numeric' => ':attribute 欄位的長度不得超過 :max 個。 ',
        'string' => ':attribute 欄位的長度不得超過 :max 個字元。 ',
    ],
    'max_digits' => ':attribute 欄位的位數不得超過 :max。 ',
    'mimes' => ':attribute 欄位必須是 :values 類型的檔案。 ',
    'mimetypes' => ':attribute 欄位必須是 :values 類型的檔案。 ',
    'min' => [
        'array' => ':attribute 欄位必須至少包含 :min 個項目。 ',
        'file' => ':attribute 欄位必須至少為 :min 千位元組。 ',
        'numeric' => ':attribute 欄位必須至少為 :min。 ',
        'string' => ':attribute 欄位必須至少包含 :min 個字元。 ',
    ],
    'min_digits' => ':attribute 欄位必須至少包含 :min 個數字。 ',
    'missing' => ':attribute 欄位必須缺失。 ',
    'missing_if' => '當 :other 為 :value 時，:attribute 欄位必須缺失。 ',
    'missing_unless' => '除非 :other 為 :value，否則 :attribute 欄位必須缺失。 ',
    'missing_with' => '當 :values 存在時，:attribute 欄位必須缺失。 ',
    'missing_with_all' => '當 :values 存在時，:attribute 欄位必須缺失。 ',
    'multiple_of' => ':attribute 欄位必須是 :value 的倍數。 ',
    'not_in' => '所選的 :attribute 無效。 ',
    'not_regex' => ':attribute 欄位格式無效。 ',
    'numeric' => ':attribute 欄位必須是數字。 ',
    'password' => [
        'letters' => ':attribute 欄位必須至少包含一個字母。 ',
        'mixed' => ':attribute 欄位必須至少包含一個大寫字母和一個小寫字母。 ',
        'numbers' => ':attribute 欄位必須至少包含一個數字。 ',
        'symbols' => ':attribute 欄位必須至少包含一個符號。 ',
        'uncompromised' => '指定的 :attribute 已發生資料外洩。請選擇其他 :attribute。 ',
    ],
    'present' => ':attribute 欄位必須存在。 ',
    'present_if' => '當 :other 為 :value 時，:attribute 欄位必須存在。 ',
    'present_unless' => '除非 :other 為 :value，否則 :attribute 欄位必須存在。 ',
    'present_with' => '當 :values 存在時，:attribute 欄位必須存在。 ',
    'present_with_all' => '當 :values 存在時，:attribute 欄位必須存在。 ',
    'prohibited' => ':attribute 欄位被禁止。 ',
    'prohibited_if' => '當 :other 為 :value 時，:attribute 欄位被禁止。 ',
    'prohibited_if_accepted' => '當 :other 被接受時，:attribute 欄位被禁止。 ',
    'prohibited_if_declined' => '當 :other 被拒絕時，:attribute 欄位是禁止的。 ',
    'prohibited_unless' => '除非 :values 中包含 :other，否則 :attribute 欄位是禁止的。 ',
    'prohibits' => ':attribute 欄位禁止 :other 出現。 ',
    'regex' => ':attribute 欄位格式無效。 ',
    'required' => ':attribute 欄位是必填項。 ',
    'required_array_keys' => ':attribute 欄位必須包含 :values 的項目。 ',
    'required_if' => '當 :other 為 :value 時，:attribute 欄位是必填項。 ',
    'required_if_accepted' => '當 :other 被接受時，:attribute 欄位是必填項。 ',
    'required_if_declined' => '當:other 被拒絕。 ',
    'required_unless' => '除非 :values 中包含 :other，否則 :attribute 欄位為必填項。 ',
    'required_with' => '當 :values 存在時，:attribute 欄位為必填項。 ',
    'required_with_all' => '當 :values 存在時，:attribute 欄位為必填項。 ',
    'required_without' => '當 :values 不存在時，:attribute 欄位為必填項。 ',
    'required_without_all' => '當所有 :values 均不存在時，:attribute 欄位為必填項。 ',
    'same' => ':attribute 欄位必須與 :other 相符。 ',
    'size' => [
        'array' => ':attribute 欄位必須包含 :size 個項目。 ',
        'file' => ':attribute 欄位必須為 :size 千位元組。 ',
        'numeric' => ':attribute 欄位必須為 :size。 ',
        'string' => ':attribute 欄位必須為 :size 個字元。 ',
    ],
    'starts_with' => ':attribute 欄位必須以下列值之一開頭：:values。 ',
    'string' => ':attribute 欄位必須是字串。 ',
    'timezone' => ':attribute 欄位必須是有效的時區。 ',
    'unique' => ':attribute 已被佔用。 ',
    'uploaded' => ':attribute 上傳失敗。 ',
    'uppercase' => ' :attribute 欄位必須大寫。 ',
    'url' => ':attribute 欄位必須是有效的 URL。 ',
    'ulid' => ':attribute 欄位必須是有效的 ULID。 ',
    'uuid' => ':attribute 欄位必須是有效的 UUID。 ',
    /*
    |------------------------------------------------------------------------------------------------
    | 自訂驗證語言行
    |------------------------------------------------------------------------------------------------
    |
    | 您可以在此處使用約定「attribute.rule」為屬性指定自訂驗證訊息。
    | 這樣可以快速為給定的屬性規則指定特定的自訂語言行。
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |----------------------------------------------------------------------------------------
    | 自訂驗證屬性
    |--------------------------------------------------------------------------------------------------------
    |
    | 以下語言行用於將屬性佔位符替換為更易於閱讀的內容，例如將“email”替換為“E-Mail Address”。這有助於我們使消息更具表現力。
    |
    */

    'attributes' => [],

];
