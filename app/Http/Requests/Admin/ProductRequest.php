<?php

namespace App\Http\Requests\Admin;

class ProductRequest extends BaseFormRequest
{
    /**
     * 檢查當前使用者是否有權限送出此表單
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 定義表單欄位的後端驗證規則
     */
    public function rules(): array
    {
        return [
            'cat_id'        => 'required|exists:product_category,cat_id',
            'is_visible'    => 'nullable|boolean',
            'display_order' => 'nullable|integer',

            // 💰 新增價格驗證：必須填寫、必須是整數、最低 0 元起
            'price'         => 'required|integer|min:0',

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
            'cat_id.required'            => '請選擇一個分類。',
            'desc.*.title.required_with' => '標題不能空著喔！',

            // 💰 新增價格的友善報錯提示
            'price.required'             => '商品的價格一定要填寫喔！',
            'price.integer'              => '價格只能填寫整數，請勿輸入小數點或特殊符號。',
            'price.min'                  => '價格金額不能小於 0 元。',
        ]);
    }
}
