<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\BaseFormRequest;

/**
 * 聯絡我們 - 後台回覆驗證器
 * 負責處理管理員回覆時的資料正確性檢查
 */
class ContactRequest extends BaseFormRequest
{
    /**
     * 判斷使用者是否有權限發起此請求
     * @return bool
     */
    public function authorize(): bool
    {
        // 實務上會交給 Controller 的中間件處理，這裡直接回傳 true
        return true;
    }

    /**
     * 定義驗證規則
     * @return array
     */
    public function rules(): array
    {
        return [
            // 回覆內容必填，且因為是 Summernote HTML，設定最小長度確保不是空白標籤
            'reply_content' => 'required|string|min:10',
        ];
    }

    /**
     * 定義錯誤訊息 (人話版本)
     * @return array
     */
    public function messages(): array
    {
        return [
            'reply_content.required' => '請輸入回覆內容，不能只有空白喔！',
            'reply_content.min'      => '回覆內容太短了，請至少輸入 10 個字以示誠意。',
        ];
    }
}
