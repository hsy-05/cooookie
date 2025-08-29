$(document).ready(function () {
    // 初始化 Summernote
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
                        contents:
                            '<i class="note-icon-unorderedlist"></i> 範本',
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

        // 圖片上傳處理
        callbacks: {
            onImageUpload: function (files) {
                const editor = $(this);
                // uploadImage(files[0], editor);
            },
        },
    });

    // 上傳圖片的 AJAX 方法
    function uploadImage(file, editor) {
        let data = new FormData();
        data.append("image", file);

        $.ajax({
            url: `${BASE_URL}/admin/upload-image`, // 上傳圖片的 Laravel 路由
            method: "POST",
            data: data,
            contentType: false,
            processData: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                editor.summernote("insertImage", response.url);
            },
            error: function () {
                alert("上傳圖片失敗");
            },
        });
    }

    // 綁定 modal 裡的範本項目點擊事件
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
