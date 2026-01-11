{{-- 定義單一橫列 --}}
<tr class="tree-row" data-id="{{ $cat->cat_id }}"
    data-parent="{{ $cat->parent_id == 0 || is_null($cat->parent_id) ? 0 : $cat->parent_id }}"
    data-level="{{ $level }}">

    <td class="category-name">
        {{-- 1. 根據層級產生縮排空白 --}}
        @for ($i = 0; $i < $level; $i++)
            <span class="tree-indent"></span>
        @endfor

        {{-- 2. 展開/收合圖示 --}}
        @if ($cat->children && $cat->children->count() > 0)
            {{-- 預設展開狀態使用 chevron-down --}}
            <i class="fas fa-chevron-down text-primary btn-toggle-tree mr-1" data-id="{{ $cat->cat_id }}"></i>
        @else
            {{-- 無子分類之末端節點 --}}
            <i class="fas fa-minus text-muted mr-1"></i>
        @endif

        {{-- 3. 分類名稱 (粗體由 CSS 根據 data-level="0" 決定) --}}
        @foreach ($cat->descs as $d)
            <span class="category-name-text">
                {{ $d->name }}
            </span>
        @endforeach
    </td>

    <td class="text-center">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input toggle-boolean-switch" id="newsSwitch{{ $cat->cat_id }}"
                data-id="{{ $cat->cat_id }}" data-model="NewsCategory" data-field="is_visible"
                {{ $cat->is_visible ? 'checked' : '' }}>
            <label class="custom-control-label" for="newsSwitch{{ $cat->cat_id }}"></label>
        </div>
    </td>

    <td class="text-center">{{ $cat->display_order }}</td>
    <td class="text-center">{{ $cat->updated_at->format('Y-m-d H:i') }}</td>

    <td class="text-center">
        {{-- 確保 btn-group 內部的元素對齊方式一致 --}}
        <div class="btn-group align-items-center">
            {{-- 編輯按鈕 --}}
            <a href="{{ route('admin.news_category.edit', $cat->cat_id) }}"
                class="btn btn-sm btn-warning d-flex align-items-center mr-1">
                <i class="fas fa-pencil-alt mr-1"></i> 編輯
            </a>

            {{-- 刪除表單 --}}
            <form action="{{ route('admin.news_category.destroy', $cat->cat_id) }}" method="POST"
                id="deleteForm{{ $cat->cat_id }}" class="m-0 p-0"> {{-- 強制表單邊距歸零 --}}
                @csrf
                @method('DELETE')

                <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-id="{{ $cat->cat_id }}"
                    data-title="確定刪除這筆資料嗎？" data-text="刪除後將無法恢復！">
                    <i class="fas fa-trash-alt mr-1"></i> 刪除
                </button>
            </form>
        </div>
    </td>
</tr>

{{-- 4. 遞迴子分類 --}}
@if ($cat->children && $cat->children->count() > 0)
    @foreach ($cat->children as $child)
        @include('admin.news_category.item_row', ['cat' => $child, 'level' => $level + 1])
    @endforeach
@endif
