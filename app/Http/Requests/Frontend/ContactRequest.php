<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 前台聯絡表單驗證器
 * 負責檢查使用者輸入的內容是否正確，以及 reCAPTCHA Token 是否存在。
 */
class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 定義驗證規則
     * @return array
     */
    public function rules(): array
    {
        return [
            'fullname'        => 'required|max:70',
            'email'           => 'required|email|max:190',
            'subject'         => 'required|max:120',
            'content'         => 'required|string',
            'recaptcha_token' => 'required', // Google 驗證碼 Token 必須存在
        ];
    }

    /**
     * 錯誤訊息翻譯
     * @return array
     */
    public function messages(): array
    {
        return [
            'fullname.required'        => '請填寫您的姓名',
            'email.email'              => '電子郵件格式不正確',
            'recaptcha_token.required' => '安全性檢查未通過，請重新整理頁面。',
        ];
    }
}
