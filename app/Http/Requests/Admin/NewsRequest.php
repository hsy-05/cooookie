<?php

namespace App\Http\Requests\Admin;

// 注意：這裡改為繼承剛剛建立的 BaseFormRequest
class NewsRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cat_id'        => 'required|exists:news_category,cat_id',
            'is_visible'    => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            // 直接呼叫父類別定義好的規則
            'image_url'     => $this->getImageRules(),
            'desc'          => 'nullable|array',
            'desc.*.title'  => 'required_with:desc.*|string|max:255',
        ];
    }

    /**
     * 自定義錯誤訊息 (讓後台報錯講人話)
     */
    public function messages(): array
    {
        // 合併「圖片訊息」與「原本標題訊息」
        return array_merge($this->getImageMessages(), [
            'cat_id.required' => '請選擇一個分類。',
            'desc.*.title.required_with' => '標題不能空著喔！',
        ]);
    }
}
