@extends('adminlte::page')

@section('title', '廣告管理')

@section('content_header')
    <h1>廣告列表</h1>
@stop

@section('content')
    <div class="text-right mb-3">
        <a href="{{ route('admin.advert.create') }}" class="btn btn-light">新增廣告</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <!-- <th class="text-center">廣告編號</th> -->
                <th class="text-center">廣告名稱</th>
                <th class="text-center px-width-150 hidden-xs">分類</th>
                <th class="text-center px-width-150 hidden-md">廣告圖</th>
                <th class="text-center px-width-150 hidden-md">排序</th>
                    <th class="text-center px-width-150 hidden-xs">是否顯示</th>
                    <th class="text-center px-width-120 table-actions">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($adverts as $advert)
                <tr>
                    {{-- <td>{{ $advert->adv_id }}</td> --}}
                    <td>{{ $advert->descs->first()->adv_name ?? '--' }}</td>
                    <td class="hidden-xs">{{ $advert->category->cat_code }}</td>
                    <td class="hidden-md">
                        @if ($advert->adv_img_url)
                            <img src="{{ asset('storage/' . $advert->adv_img_url) }}" width="100" alt="廣告圖">
                        @else
                            無圖片
                        @endif
                    </td>
                    <td class="hidden-md">{{ $advert->display_order }}</td>
                    <td class="text-center hidden-xs">
                        {{-- AdminLTE Custom Switch Element --}}
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                id="advertSwitch{{ $advert->adv_id }}"
                                data-id="{{ $advert->adv_id }}"
                                data-model="Advert" {{-- 指定模型名稱 --}}
                                data-field="is_visible" {{-- 指定要更新的欄位 --}}
                                {{ $advert->is_visible ? 'checked' : '' }}>
                            <label class="custom-control-label" for="advertSwitch{{ $advert->adv_id }}"></label>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.advert.edit', $advert->adv_id) }}" class="btn btn-primary btn-sm">編輯</a>
                        <form action="{{ route('admin.advert.destroy', $advert->adv_id) }}" method="POST" style="display:inline;" onsubmit="return confirm('確定要刪除嗎？')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $adverts->links() }}
@stop

@section('js')
    <script>
        $(function() {
            // 監聽所有帶有 'toggle-boolean-switch' 類別的 checkbox 的 change 事件
            $('.toggle-boolean-switch').on('change', function() {
                const switchElement = $(this);
                const id = switchElement.data('id');
                const model = switchElement.data('model');
                const field = switchElement.data('field');
                // 將 true/false 轉換為 1/0，以便 Laravel 的 boolean 驗證器正確處理
                const value = switchElement.is(':checked') ? 1 : 0;

                // 發送 AJAX 請求
                $.ajax({
                    url: "{{ route('admin.toggle.boolean') }}", // 通用 AJAX 路由
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // CSRF token
                        model: model,
                        id: id,
                        field: field,
                        value: value // 使用轉換後的 1 或 0
                    },
                    success: function(response) {
                        // 顯示成功訊息
                        Swal.fire({
                            icon: 'success',
                            title: '成功',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },
                    error: function(xhr) {
                        // 顯示錯誤訊息
                        let errorMessage = '狀態更新失敗。';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                            // 如果後端返回了驗證錯誤，可以顯示更詳細的訊息
                            if (xhr.responseJSON.errors) {
                                for (const key in xhr.responseJSON.errors) {
                                    errorMessage += '\n' + xhr.responseJSON.errors[key].join(', ');
                                }
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: '錯誤',
                            text: errorMessage,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        // 如果更新失敗，將 switch 恢復到之前的狀態
                        switchElement.prop('checked', !value);
                    }
                });
            });
        });
    </script>
@stop
