@extends('adminlte::page')

@section('title', '語言設定')

@section('content_header')
    <h1>語言設定</h1>
@stop

@section('content')
    <a href="{{ route('admin.languages.create') }}" class="btn btn-primary mb-2">新增語系</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        {{-- <div class="card-header">
            <h3 class="card-title">紀錄列表</h3>
            </div> --}}

        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th class="text-center">名稱</th>
                        <th class="text-center px-width-150 hidden-lg">代碼</th>
                        <th class="text-center px-width-150 hidden-md">排序</th>
                        <th class="text-center px-width-150 hidden-xs">啟用</th>
                        <th class="text-center px-width-150 hidden-lg">顯示範圍</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($langs as $lang)
                        <tr>
                            <td>{{ $lang->lang_id }}</td>
                            <td>{{ $lang->name }} ({{ $lang->alias }})</td>
                            <td class="text-center hidden-lg">{{ $lang->code }}</td>
                            <td class="text-center hidden-md">{{ $lang->display_order }}</td>
                            <td class="text-center hidden-xs">{{ $lang->enabled ? '是' : '否' }}</td>
                            <td class="text-center hidden-lg">{{ $lang->display_scope }}</td>

                            {{-- 操作欄位 --}}
                            <td class="text-center">
                                <div class="table-actions-container">
                                    {{-- 編輯按鈕 --}}
                                    @can('languages.edit')
                                        <a href="{{ route('admin.languages.edit', $lang->lang_id) }}"
                                            class="btn btn-sm btn-warning" title="編輯">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    {{-- 刪除按鈕 --}}
                                    @can('languages.delete')
                                        <form action="{{ route('admin.languages.destroy', $lang->lang_id) }}" method="POST"
                                            style="display:inline-block;" id="deleteForm{{ $lang->lang_id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                data-id="{{ $lang->lang_id }}" title="刪除">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function() {
            $(document).on('click', '.js-delete-btn', function() {
                // 從 data 屬性抓取參數，若無則給予預設值
                const id = $(this).data('id');
                const title = $(this).data('title') || '確定刪除這筆資料嗎？';
                const text = $(this).data('text') || '刪除後將無法恢復！';

                if (!id) {
                    console.warn('警告：刪除按鈕缺少 data-id 參數。');
                    return;
                }

                confirmDelete(id, title, text);
            });
        });
    </script>
@stop
