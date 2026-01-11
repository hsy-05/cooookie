@extends('adminlte::page')

@section('title', '消息分類管理')

@section('content_header')
    <h1>消息分類管理</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/backend.css') }}">
    <style>
        /* 樹型表格專用樣式 */
        .tree-indent {
            width: 30px;
            display: inline-block;
        }

        /* 層級縮排單位 */
        .category-name {
            text-align: left !important;
        }

        /* 分類名稱靠左對齊才有層級感 */
    </style>
@stop

@section('content')
    <x-admin.page-message>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.news_category.create') }}" class="btn btn-primary ml-auto">
                <i class="fas fa-plus-square"></i> 新增分類
            </a>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="bg-light">
                <tr>
                    <th class="text-left">名稱</th> {{-- 樹狀結構名稱必須靠左 --}}
                    <th class="text-center px-width-150">是否顯示</th>
                    <th class="text-center px-width-100">排序</th>
                    <th class="text-center px-width-150">更新時間</th>
                    <th class="text-center px-width-200">操作</th>
                </tr>
            </thead>
            <tbody>
                {{-- 開始遞迴渲染，傳入初始層級 level = 0 --}}
                @forelse ($categories as $cat)
                    @include('admin.news_category.item_row', ['cat' => $cat, 'level' => 1])
                @empty
                    <tr>
                        <td colspan="5" class="text-center">目前沒有任何記錄。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.page-message>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /**
             * 刪除按鈕監聽
             */

            /**
             * 刪除按鈕監聽（統一寫法）
             */
            const deleteButtons = document.querySelectorAll('.js-delete-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {

                    const id = this.dataset.id;
                    const title = this.dataset.title || '確定要刪除嗎？';
                    const text = this.dataset.text || '刪除後無法恢復！';

                    /**
                     * 防呆：檢查是否有子分類
                     * 若沒有樹狀資料，length 會是 0
                     */
                    const hasChildren = $(`.tree-row[data-parent="${id}"]`).length > 0;

                    if (hasChildren) {
                        showAlert(
                            'warning',
                            '無法刪除',
                            '該分類下仍有子分類，請先刪除或移動子分類後再試。',
                            false,
                            'center',
                            true,
                            '我知道了',
                            0
                        );
                        return;
                    }

                    confirmDelete(id, title, text);
                });
            });


            /**
             * 樹狀摺疊切換
             */
            $(document).on('click', '.btn-toggle-tree', function() {
                const $icon = $(this);
                const catId = $icon.data('id');
                const $children = $(`.tree-row[data-parent="${catId}"]`);

                if ($children.is(':visible')) {
                    // 如果目前是顯示的 -> 隱藏所有子孫
                    recursiveHide(catId);
                    $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                } else {
                    // 如果目前是隱藏的 -> 只顯示直接子代
                    $children.fadeIn(200);
                    $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                }
            });

            /**
             * 遞迴隱藏函式：確保父層關閉時，底下的所有層級都一起關閉
             */
            function recursiveHide(parentId) {
                const $children = $(`.tree-row[data-parent="${parentId}"]`);
                $children.each(function() {
                    const childId = $(this).data('id');
                    $(this).hide();

                    // 把子層的圖示也轉回「右箭頭」狀態
                    const $subIcon = $(this).find('.btn-toggle-tree');
                    $subIcon.removeClass('fa-chevron-down').addClass('fa-chevron-right');

                    recursiveHide(childId); // 繼續往更深層隱藏
                });
            }
        });
    </script>
@stop
