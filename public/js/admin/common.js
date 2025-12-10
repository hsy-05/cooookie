$(function() {
    // 監聽所有帶有 'toggle-boolean-switch' 類別的 checkbox 的 change 事件
    $('.toggle-boolean-switch').on('change', function() {
        const switchElement = $(this);
        const id = switchElement.data('id'); // 從 data-id 屬性獲取記錄 ID
        const model = switchElement.data('model'); // 從 data-model 屬性獲取模型名稱
        const field = switchElement.data('field'); // 從 data-field 屬性獲取要更新的欄位名稱
        const value = switchElement.is(':checked') ? 1 : 0;

        // 發送 AJAX 請求
        $.ajax({
            url: window.toggleBooleanUrl, // 從 Blade 生成的全域變數
            method: 'POST',
            data: {
                _token: window.csrfToken, // 從 Blade 生成的全域變數
                model: model,
                id: id,
                field: field,
                value: value
            },
            success: function(response) {
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
                let errorMessage = '狀態更新失敗。';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
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
                switchElement.prop('checked', !value);
            }
        });
    });
});
