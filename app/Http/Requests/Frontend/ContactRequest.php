<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactRequest extends FormRequest
{
    /**
     * 驗證權限控管
     * @return bool 允許所有人發送此請求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 定義表單欄位驗證規則
     * @return array 驗證規則陣列
     */
    public function rules(): array
    {
        return [
            'fullname'        => 'required|string|max:70',
            'tel'             => 'required|string|max:30', // 補上與 Blade 對應的電話欄位驗證
            'email'           => 'required|email|max:190',
            'subject'         => 'required|string|max:120',
            'content'         => 'required|string',
            'recaptcha_token' => 'required|string',
        ];
    }

    /**
     * 表單欄位錯誤訊息中文化翻譯
     * @return array 錯誤訊息對照表
     */
    public function messages(): array
    {
        return [
            'fullname.required'        => '請填寫您的姓名。',
            'fullname.max'             => '姓名長度超出了系統限制。',
            'tel.required'             => '請填寫您的聯絡電話。',
            'tel.max'                  => '聯絡電話長度超出了系統限制。',
            'email.required'           => '請填寫您的電子郵件。',
            'email.email'              => '電子郵件格式不正確。',
            'email.max'                => '電子郵件長度超出了系統限制。',
            'subject.required'         => '請填寫諮詢主題。',
            'subject.max'              => '諮詢主題長度超出了系統限制。',
            'content.required'         => '請填寫諮詢內容。',
            'recaptcha_token.required' => '安全性檢查未通過，請重新整理頁面。',
        ];
    }

    /**
     * 處理驗證失敗的客製化回應
     * 目的：配合前端 AJAX 邏輯，包裝成一致的 JSON 格式，並回傳第一筆錯誤訊息
     *
     * @param Validator $validator 驗證器實體
     * @throws HttpResponseException 直接中斷程序並拋出符合前端預期的 JSON 回應
     */
    protected function failedValidation(Validator $validator)
    {
        // 取得所有錯誤訊息陣列中的第一筆文字內容
        $firstMessage = $validator->errors()->first();

        // 強制包裝成標準 JSON 回應格式，並設定 HTTP 狀態碼為 422
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $firstMessage
            ], 422)
        );
    }
}
