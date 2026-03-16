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
        'settings' => 'required|array',
        // 對於每一個設定項，根據其特性進行基礎驗證
        'settings.*' => [
            'nullable',
            function ($attribute, $value, $fail) {
                // 如果是檔案上傳
                if (request()->hasFile($attribute)) {
                    $file = request()->file($attribute);
                    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array(strtolower($file->getClientOriginalExtension()), $allowedExt)) {
                        $fail('不支援的檔案格式，僅限：' . implode(', ', $allowedExt));
                    }
                    if ($file->getSize() > 2 * 1024 * 1024) {
                        $fail('圖片大小不能超過 2MB');
                    }
                }
            }
        ],
    ];
}
}
