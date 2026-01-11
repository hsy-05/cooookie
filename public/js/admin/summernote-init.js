/**
 * Summernote 編輯器初始化設定
 * 適用於：Laravel 12 + AdminLTE
 * 功能：圖片 AJAX 上傳、HTML 範本插入、響應式配置
 */
$(document).ready(function () {
    // --- 1. 環境變數設定 ---
    // 優先從 HTML 的 meta tag 抓取根網址，避免子目錄 (如 /cooookie) 導致的 404 錯誤
    const BASE_URL =
        $('meta[name="base-url"]').attr("content") || window.location.origin;
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");

    // --- 2. 初始化 Summernote ---
    $(".summernote").summernote({
        height: 300,
        lang: "zh-TW",
        placeholder: "請輸入內容...",

        // 工具列設定，加入自訂的「範本」按鈕
        toolbar: [
            // ======================
            // 樣式相關（區塊層級）
            // ======================
            ["style", ["style"]],
            /*
                style：
                - 套用區塊樣式
                - 預設包含：
                    - p（段落）
                    - blockquote（引用）
                    - pre（程式碼區塊）
                    - h1 ~ h6（標題）
                - 實務用途：
                    - 編輯「文章結構」
                    - 對 SEO 很重要（h1~h6）
                - 進階可加：
                    - div（一般區塊）
                    - custom style class（公司自訂 class）
            */

            // ======================
            // 文字格式（行內樣式）
            // ======================
            [
                "font",
                [
                    "bold",
                    "italic",
                    "underline",
                    "strikethrough",
                    "superscript",
                    "subscript",
                    "clear",
                ],
            ],
            /*
                bold        → 粗體 <strong>
                italic      → 斜體 <em>
                underline   → 底線 <u>
                strikethrough → 刪除線 <s>
                superscript → 上標 <sup>
                subscript   → 下標 <sub>
                clear       → 清除所有文字格式（會移除 span / style）
                - 實務用途：
                    - 編輯重點文字
                    - clear 在後台非常重要（防止亂貼格式）
            */

            // ======================
            // 字體名稱與大小
            // ======================
            ["fontname", ["fontname"]],
            ["fontsize", ["fontsize"]],
            /*
                fontname：
                    - 選擇字體（如 Arial, Noto Sans, Times New Roman）
                    - 建議前台不要開，控制交給 CSS
                fontsize：
                    - 控制字體大小
                    - 輸出 <span style="font-size: xxpx">
                    - 後台 EDM、文章編輯可以使用
            */

            // ======================
            // 文字顏色 / 背景色
            // ======================
            ["color", ["color"]],
            /*
                color：
                - textcolor → 文字顏色
                - backcolor → 背景顏色（highlight）
                - 實際輸出是 inline style
                - 後台 OK，但前台通常過濾
            */

            // ======================
            // 段落與清單
            // ======================
            ["para", ["ul", "ol", "paragraph", "height"]],
            /*
                ul         → 無序清單 <ul>
                ol         → 有序清單 <ol>
                paragraph  → 段落對齊
                    - left, center, right, justify
                height     → 行高調整
                - 文章結構核心工具
                - SEO、可讀性
            */

            // ======================
            // 插入內容
            // ======================
            [
                "insert",
                [
                    "link",
                    "picture",
                    "video",
                    "table",
                    "hr",
                    "template",
                    "specialChar",
                ],
            ],
            /*
                link         → 插入超連結 <a>
                picture      → 插入圖片（可上傳）
                video        → 插入影片（YouTube / Vimeo）
                table        → 插入表格 <table>
                hr           → 分隔線 <hr>
                template     → 自訂範本（你的客製化）
                specialChar  → 特殊符號（©, ™, …）
                - 這區最常客製化
            */

            // ======================
            // 編輯器檢視模式 / 操作
            // ======================
            ["view", ["fullscreen", "codeview"]],
            /*
                fullscreen → 全螢幕編輯
                codeview   → HTML 原始碼模式
            */

            // ======================
            // 進階功能（可選）
            // ======================
            ["help", ["help"]],
            // ["history", ["undo", "redo"]],
            ["misc", ["print"]],
            /*
                help        → 幫助文件
                history     → undo 復原 / redo 重做
                misc        → 其他雜項
                        - print 印出頁面
            */
        ],
        // 自訂字體大小選單
        fontsize: ["8", "10", "12", "14", "16", "18", "24", "36", "48"],

        // 自訂功能按鈕定義
        buttons: {
            template: function (context) {
                const ui = $.summernote.ui;
                return ui
                    .button({
                        contents:
                            '<i class="note-icon-unorderedlist"></i> 範本',
                        tooltip: "插入內容範本",
                        click: function () {
                            // 開啟 Modal 並將編輯器實例 (context) 暫存，供稍後插入內容使用
                            $("#templateModal")
                                .data("summernote-context", context)
                                .modal("show");
                        },
                    })
                    .render();
            },
        },

        // 事件監聽
        callbacks: {
            /**
             * 當使用者拖放或選擇圖片上傳時觸發
             */
            onImageUpload: function (files) {
                if (files.length > 0) {
                    // 呼叫上傳函式，傳入第一個檔案與當前編輯器實例
                    uploadImage(files[0], $(this));
                }
            },
        },
    });

    /**
     * AJAX 上傳圖片至伺服器
     * @param {File} file 檔案物件
     * @param {Object} $editor 編輯器 jQuery 物件
     */
    function uploadImage(file, $editor) {
        let formData = new FormData();
        formData.append("image", file);

        $.ajax({
            // 使用動態 BASE_URL，確保在 /cooookie 子目錄下路徑依然正確
            url: `${BASE_URL}/admin/upload-image`,
            method: "POST",
            data: formData,
            contentType: false, // 必須為 false，告知 jQuery 不要處理 content-type
            processData: false, // 必須為 false，告知 jQuery 不要處理 data
            headers: {
                "X-CSRF-TOKEN": CSRF_TOKEN,
            },
            success: function (response) {
                if (response.url) {
                    // 執行成功：將圖片網址插入編輯器
                    $editor.summernote("insertImage", response.url);
                    console.log("Image Uploaded: " + response.url);
                } else {
                    alert("上傳失敗：後端未回傳網址");
                }
            },
            error: function (xhr) {
                console.error("Upload Error:", xhr);
                let msg = "上傳圖片失敗";

                // 根據不同的 HTTP 狀態碼提供精確回饋
                if (xhr.status === 404) {
                    msg = `找不到上傳路徑 (404)。目前嘗試請求：${BASE_URL}/admin/upload-image`;
                } else if (xhr.status === 419) {
                    msg = "CSRF Token 過期，請重新整理頁面";
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                alert(msg);
            },
        });
    }

    // --- 3. 範本插入邏輯 ---
    // 使用事件委派綁定範本項目點擊事件
    $(document).on("click", ".template-item", function () {
        const id = $(this).data("id");
        const context = $("#templateModal").data("summernote-context");

        // 取得 HTML 範本檔案內容
        $.get(`${BASE_URL}/template/tpl_${id}.html`, function (htmlContent) {
            // 使用 summernote 內建 API 將 HTML 插入游標處
            context.invoke("editor.pasteHTML", htmlContent);
            $("#templateModal").modal("hide");
        }).fail(function () {
            alert(`無法載入範本檔案：tpl_${id}.html`);
        });
    });
});
