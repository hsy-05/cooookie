@props([
    'label' => '',
    'name' => '',
    'value' => [],
    'placeholder' => '請輸入並按 Enter...'
])

<div class="form-group-item">
    {{-- 語意化標籤：如果有設定 label 才顯示 --}}
    @if($label)
        <label class="form-label">{{ $label }}</label>
    @endif

    {{--
        js-tags-input: JS 掛載點
        data-name: 對應後端接收的欄位名 (如: meta_keyword[])
        data-placeholder: 傳遞給 JS 生成 input 的提示文字
    --}}
    <div class="js-tags-input"
         data-name="{{ $name }}"
         data-placeholder="{{ $placeholder }}">

        {{--
            處理初始值：
            1. 使用 (array) 強制轉型，確保字串或空值都能安全進入 foreach
            2. 僅渲染 JS 規格需要的 .tag-item 隱藏節點
        --}}
        @foreach((array)$value as $tag)
            @if(filled($tag))
                <span class="tag-item" data-value="{{ trim($tag) }}"></span>
            @endif
        @endforeach
    </div>
</div>
