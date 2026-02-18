<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NewsCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'parent_id'     => 'nullable|integer',
            'is_visible'    => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image_url'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'desc'          => 'nullable|array',
            'desc.*.name'   => 'required_with:desc.*|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'desc.*.name.required_with' => '分類名稱別忘了填寫喔！',
            'image_url.image' => '必須上傳圖片檔案。',
        ];
    }
}
