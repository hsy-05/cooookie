/**
 * Summernote 編輯器初始化設定
 * 適用於：Laravel 12 + AdminLTE
 * 功能：圖片 AJAX 上傳、HTML 範本插入、響應式配置、動態系統設定載入
 * 補強：新增 editor_id 機制，用於追蹤未儲存的圖片。
 */
$(document).ready(function () {
    // --- 環境變數設定 ---
    const BASE_URL = $('meta[name="base-url"]').attr("content") || window.location.origin;
    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");

    /**
     * 為本次頁面生成唯一的編輯器標記 (使用時間戳記 + 隨機數)
     * 用途：讓後端知道哪些圖片屬於同一次編輯行為
     */
    const EDITOR_SESSION_ID = "editor_" + Date.now() + Math.floor(Math.random() * 1000);

    /**
     * 初始化 Summernote 實例
     * @param {Object} config 來自 API 的設定物件 {fonts, sizes, css}
     */
    function initSummernote(config) {
        // 調試用：確認傳入的設定值
        console.log("Summernote 接收到的配置：", config);

        $(".summernote").each(function () {
            const $el = $(this);
            const customHeight = $el.data("height") || 600;
            const customPlaceholder = $el.attr("placeholder") || "請輸入內容...";

            // 處理 API 可能傳回的空值或未定義
            const fontList = (config.fonts && config.fonts.trim() !== "") ? config.fonts.split(',') : ["Arial", "sans-serif"];
            const sizeList = (config.sizes && config.sizes.trim() !== "") ? config.sizes.split(',') : ["12", "14", "16", "18", "24", "36"];

            $el.summernote({
                height: customHeight,
                lang: "zh-TW",
                placeholder: customPlaceholder,

                // 套用來自系統參數的 CSS 樣式路徑
                contentsCss: config.css ? [config.css] : [],

                // 字體與字級設定
                fontNames: fontList,
                fontNamesIgnoreCheck: fontList,
                fontSizes: sizeList,

                // --- 2. 工具列設定 (完整註解保留) ---
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
                    ul          → 無序清單 <ul>
                    ol          → 有序清單 <ol>
                    paragraph   → 段落對齊
                        - left, center, right, justify
                    height      → 行高調整
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
                    // 進階功能
                    // ======================
                    ["help", ["help"]],
                    ["misc", ["print"]],
                ],

                // 自訂功能按鈕定義
                buttons: {
                    template: function (context) {
                        const ui = $.summernote.ui;
                        return ui.button({
                            contents: '<i class="note-icon-unorderedlist"></i> 範本',
                            tooltip: "插入內容範本",
                            click: function () {
                                $("#templateModal").data("summernote-context", context).modal("show");
                            },
                        }).render();
                    },
                },

                // 核心事件處理
                callbacks: {
                    /**
                     * 當使用者拖入或選擇圖片時觸發
                     */
                    onImageUpload: function (files) {
                        if (files.length > 0) {
                            // 呼叫上傳函式，並帶入本次頁面的 EDITOR_SESSION_ID
                            handleImageUpload(files[0], $(this), BASE_URL, CSRF_TOKEN, EDITOR_SESSION_ID);
                        }
                    },
                    /**
                     * 當使用者在編輯器點選圖片並按下刪除鍵時
                     */
                    onMediaDelete: function (target) {
                       // 註：實體檔案刪除目前由後端儲存時自動比對處理
                        console.log("圖片已從編輯器移除，待儲存後伺服器將自動清理實體檔案。");
                        // const imageUrl = target[0].src;
                        // if (!imageUrl.startsWith("data:")) {
                        //     handleImageDelete(imageUrl, BASE_URL, CSRF_TOKEN);
                        // }
                    },
                },
            });

            // 【關鍵防呆】將 ID 注入表單，確保按下儲存按鈕時，後端知道要 Commit 哪些圖片
            if ($el.closest('form').length > 0) {
                // 如果表單內還沒這個 ID，就塞一個 hidden input
                if ($el.closest('form').find('input[name="editor_id"]').length === 0) {
                    $el.closest('form').append(`<input type="hidden" name="editor_id" value="${EDITOR_SESSION_ID}">`);
                }
            }
        });
    }

    // --- 執行初始化：從系統設定 API 獲取配置 ---
    $.getJSON(`${BASE_URL}/admin/editor-settings`)
        .done(function (response) {
            console.log("API 請求成功，原始回應內容：", response);
            initSummernote(response);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("無法取得編輯器系統設定：", textStatus, errorThrown);
            // 失敗時使用基本的硬編碼預設值，確保編輯器仍能運作
            initSummernote({
                fonts: "Arial,Microsoft JhengHei",
                sizes: "12,14,16,18,24,36",
                css: ""
            });
        });

    // --- 範本插入邏輯 ---
    $(document).on("click", ".template-item", function () {
        const id = $(this).data("id");
        const context = $("#templateModal").data("summernote-context");
        if (!context) return alert("系統錯誤：找不到編輯器實例");

        $.get(`${BASE_URL}/template/tpl_${id}.html`, function (htmlContent) {
            context.invoke("editor.pasteHTML", htmlContent);
            $("#templateModal").modal("hide");
        }).fail(function () {
            alert("範本載入失敗，請檢查檔案是否存在。");
        });
    });
});

/**
 * AJAX 上傳圖片至伺服器
 * @param {File} file 檔案物件
 * @param {Object} $editor 編輯器 jQuery 物件
 * @param {string} baseUrl 網站根路徑
 * @param {string} token CSRF Token
 * @param {string} editorId 本次編輯的唯一編號
 */
function handleImageUpload(file, $editor, baseUrl, token, editorId) {
    let formData = new FormData();
    formData.append("image", file);
    formData.append("editor_id", editorId); // 【關鍵】傳送 ID 給後端記錄暫存

    $.ajax({
        url: `${baseUrl}/admin/upload-editor-image`,
        method: "POST",
        data: formData,
        contentType: false,
        processData: false,
        headers: { "X-CSRF-TOKEN": token },
        success: function (response) {
            if (response.success && response.url) {
                $editor.summernote("insertImage", response.url);
            } else {
                alert("圖片上傳失敗：" + (response.error || "未知錯誤"));
            }
        },
        error: function () {
            alert("伺服器通訊錯誤，請稍後再試。");
        },
    });
}

/**
 * 【X 無用】
 * AJAX 刪除伺服器上的圖片檔案
 * @param {string} url 圖片的完整網址
 */
function handleImageDelete(url, baseUrl, token) {
    $.ajax({
        url: `${baseUrl}/admin/delete-editor-image`,
        method: "POST",
        data: { image_url: url },
        headers: { "X-CSRF-TOKEN": token }
    });
}
