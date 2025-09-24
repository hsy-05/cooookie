$(document).ready(function () {
    // 定義 BASE_URL（如果未定義，從視圖中傳入）
    if (typeof BASE_URL === 'undefined') {
        BASE_URL = window.location.origin; // 預設使用當前主機，如 http://localhost:82
    }

    // 初始化 Summernote（自動處理所有 .summernote 元素）
    $(".summernote").summernote({
        height: 300,
        lang: "zh-TW",

        // 工具列設定，加入自訂的「範本」按鈕
        toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "italic", "underline", "clear"]],
            ["fontsize", ["fontsize"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["insert", ["link", "picture", "video", "template"]],
            ["view", ["fullscreen", "codeview", "help"]],
        ],

        // 自訂字體大小選單
        fontsize: ["8", "10", "12", "14", "16", "18", "24", "36", "48"],

        // 自訂工具按鈕
        buttons: {
            template: function (context) {
                const ui = $.summernote.ui;

                // 建立一個「📄 範本」按鈕，點擊後開啟 Modal
                return ui
                    .button({
                        contents: '<i class="note-icon-unorderedlist"></i> 範本',
                        tooltip: "插入範本",
                        click: function () {
                            $("#templateModal")
                                .data("summernote-context", context)
                                .modal("show");
                        },
                    })
                    .render();
            },
        },

        // 圖片上傳處理（修正：取消註解，並使用 context 插入）
        callbacks: {
            onImageUpload: function (files, context) {  // 修正：接收 files 和 context
                const editor = $(this);  // 當前編輯器
                uploadImage(files[0], editor, context);  // 修正：取消註解，呼叫上傳
            },
        },
    });

    // 上傳圖片的 AJAX 方法（修正：改進錯誤處理，使用 context 插入）
    function uploadImage(file, editor, context) {
        let data = new FormData();
        data.append("image", file);

        $.ajax({
            url: `${BASE_URL}/admin/upload-image`, // 上傳圖片的 Laravel 路由
            method: "POST",
            data: data,
            contentType: false,
            processData: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),  // CSRF token
            },
            success: function (response) {
                if (response.url) {
                    // 插入圖片到當前編輯器（使用 context.invoke 更可靠，支援多實例）
                    console.log('圖片上傳成功：', response.url);  // 調試用
                    editor.summernote("insertImage", response.url);
                } else {
                    alert('上傳回應異常：' + (response.message || '未知錯誤'));
                }
            },
            error: function (xhr) {
                console.error('上傳錯誤：', xhr);  // 調試：顯示詳細錯誤
                let errorMsg = '上傳圖片失敗';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    errorMsg = 'CSRF token 無效，請重新整理頁面';
                } else if (xhr.status === 404) {
                    errorMsg = '上傳路由不存在，請檢查後端設定';
                }
                alert(errorMsg);  // 或使用 SweetAlert2 顯示
            },
        });
    }

    // 綁定 modal 裡的範本項目點擊事件（保持不變）
    $(document).on("click", ".template-item", function () {
        const id = $(this).data("id");
        const context = $("#templateModal").data("summernote-context");

        // JS 裡使用這個 base URL
        $.get(`${BASE_URL}/template/tpl_${id}.html`, function (res) {
            context.invoke("editor.pasteHTML", res); // 插入內容
            $("#templateModal").modal("hide"); // 關閉 modal
        }).fail(function () {
            alert(`無法載入範本 tpl_${id}`);
        });
    });
});
