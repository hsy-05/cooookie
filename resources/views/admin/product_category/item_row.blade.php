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
        @foreach ($cat->descs as $desc)
            <span class="category-name-text">
                {{ $desc->name }}
            </span>
        @endforeach
    </td>

    <td class="text-center">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input toggle-boolean-switch" id="productSwitch{{ $cat->cat_id }}"
                data-id="{{ $cat->cat_id }}" data-model="ProductCategory" data-field="is_visible"
                {{ $cat->is_visible ? 'checked' : '' }}>
            <label class="custom-control-label" for="productSwitch{{ $cat->cat_id }}"></label>
        </div>
    </td>

    <td class="text-center">{{ $cat->display_order }}</td>
    <td class="text-center">{{ $cat->updated_at->format('Y-m-d H:i') }}</td>

    {{-- 操作欄位 --}}
    <td class="text-center">
        <div class="table-actions-container">
            {{-- 編輯按鈕 --}}
            @can('product_category.edit')
                <a href="{{ route('admin.product_category.edit', $cat->cat_id) }}" class="btn btn-sm btn-warning" title="編輯">
                    <i class="fas fa-edit"></i>
                </a>
            @endcan

                {{-- 刪除按鈕 --}}
                @can('product_category.delete')
                    @if ($cat->children->isEmpty()) {{-- 這裡改成判斷當前分類的子項 --}}
                        <form action="{{ route('admin.product_category.destroy', $cat->cat_id) }}" method="POST" id="deleteForm{{ $cat->cat_id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                    data-id="{{ $cat->cat_id }}"
                                    title="刪除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @else
                        {{-- 不可刪除的狀態：樣式改為 secondary 並加上 disabled --}}
                        <button type="button" class="btn btn-sm btn-secondary" disabled
                                title="該分類下仍有子分類，請先刪除或移動子分類。">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                @endcan
        </div>
    </td>
</tr>

{{-- 4. 遞迴子分類 --}}
@if ($cat->children && $cat->children->count() > 0)
    @foreach ($cat->children as $child)
        @include('admin.product_category.item_row', ['cat' => $child, 'level' => $level + 1])
    @endforeach
@endif
