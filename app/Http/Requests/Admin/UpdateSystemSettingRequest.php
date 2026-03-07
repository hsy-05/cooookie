<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingRequest extends FormRequest
{
    /**
     * 權限判斷：確保只有登入者或管理者可操作
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 定義驗證規則：這是防止惡意寫入的關鍵
     */
    public function rules(): array
    {
        return [
            // 驗證 settings 必須是陣列
            'settings' => 'required|array',
            // 限制圖片格式與大小 (2MB)
            'settings.*' => 'nullable|sometimes',
            'settings.*' => [
                function ($attribute, $value, $fail) {
                    if (request()->hasFile($attribute)) {
                        $file = request()->file($attribute);
                        if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'webp'])) {
                            $fail('不支援的檔案格式。');
                        }
                        if ($file->getSize() > 2048 * 1024) {
                            $fail('檔案大小不能超過 2MB。');
                        }
                    }
                },
            ],
        ];
    }
}
