// 設定 jQuery AJAX 全域 Header，這樣就不用在每個 data 裡傳 _token 了
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(function () {
    // === 執行初始化功能 ===
    initGlobalDelete();         // 單筆刪除 (含分類防呆)
    initTreeToggle();           // 樹狀分類摺疊邏輯
    initBatchDelete();         // 初始化批次刪除
    initImagePreview();       // 初始化圖片預覽
    initAsyncImageDelete();   // 初始化異步圖片刪除
    initImageUploadStats();   // 初始化上傳資訊更新
    handleToggleBooleanSwitch(); // 初始化開關切換
});

/**
 * 1. 全域單筆刪除監聽 (關鍵：包含分類子項檢查)
 */
function initGlobalDelete() {
    $(document).on('click', '.js-delete-btn', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const id = $btn.data('id');
        const title = $btn.data('title') || '確定要刪除嗎？';
        const text = $btn.data('text') || '刪除後將無法恢復！';

        // --- 分類防呆邏輯 ---
        // 檢查頁面上是否有標記為該 ID 子層的 tree-row
        const hasChildren = $(`.tree-row[data-parent="${id}"]`).length > 0;

        if (hasChildren) {
            showAlert(
                'warning',
                '無法刪除',
                '該分類下仍有子分類，請先刪除或移動子分類後再試。',
                false, 'center', true, '我知道了'
            );
            return; // 攔截，不執行刪除
        }

        // 呼叫刪除確認視窗
        confirmDelete(id, title, text);
    });
}

/**
 * 2. 樹狀分類摺疊驅動
 * 用途：點擊箭頭展開或收合子分類
 */
function initTreeToggle() {
    $(document).on('click', '.btn-toggle-tree', function() {
        const $icon = $(this);
        const catId = $icon.data('id');
        const $children = $(`.tree-row[data-parent="${catId}"]`);

        if ($children.is(':visible')) {
            // 目前是展開狀態 -> 執行遞迴隱藏（確保所有子孫都關閉）
            recursiveHideTree(catId);
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else {
            // 目前是隱藏狀態 -> 只顯示直接子代（保持結構清晰）
            $children.fadeIn(200);
            $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        }
    });
}

/**
 * 樹狀結構遞迴隱藏工具
 * @param {number} parentId - 父層 ID
 */
function recursiveHideTree(parentId) {
    const $children = $(`.tree-row[data-parent="${parentId}"]`);

    $children.each(function() {
        const childId = $(this).data('id');
        $(this).hide();

        // 將子層圖示狀態也重設為「右箭頭」
        const $subIcon = $(this).find('.btn-toggle-tree');
        $subIcon.removeClass('fa-chevron-down').addClass('fa-chevron-right');

        // 繼續往下找更深層的子項目
        recursiveHideTree(childId);
    });
}

/**
 * 2. 全域批次刪除監聽
 * 用途：處理勾選多筆後的刪除行為
 */
function initBatchDelete() {
    const $checkAll = $('#checkAll');
    const $checkboxes = $('.row-checkbox');
    const $batchBtn = $('#batchDeleteBtn');

    if (!$checkAll.length) return; // 防呆：頁面沒這個 ID 就不執行

    // 更新批次按鈕的可點擊狀態
    const updateBtnState = () => {
        const checkedCount = $('.row-checkbox:checked').length;
        $batchBtn.prop('disabled', checkedCount === 0);
    };

    // 全選/全不選
    $checkAll.on('change', function () {
        $checkboxes.prop('checked', this.checked);
        updateBtnState();
    });

    // 單個勾選連動
    $checkboxes.on('change', updateBtnState);

    // 按下批次刪除按鈕
    $batchBtn.on('click', function (e) {
        e.preventDefault();
        const count = $('.row-checkbox:checked').length;

        showAlert('warning', '批次刪除確認', `您已選取 ${count} 筆資料，確定要刪除嗎？`, false, 'center', true, '確定批次刪除', 0, {
            showCancelButton: true,
            cancelButtonText: '取消',
            preConfirm: () => {
                $('#batchDeleteForm').submit(); // 假設你的批次表單 ID 是這個
            }
        });
    });
}

/**
 * 3. 圖片預覽功能
 * 用途：點擊「查看」按鈕彈出圖片 Modal
 */
function initImagePreview() {
    $(document).on('click', '.js-open-preview', function () {
        const targetUrl = $(this).data('url');
        const $modal = $('#globalImageModal');
        const $img = $('#globalPreviewImg');

        if (!targetUrl) return showAlert('error', '錯誤', '找不到圖片路徑', true);

        $img.attr('src', targetUrl);
        $modal.modal('show');
    });
}

/**
 * 4. 編輯頁 - 異步刪除圖片處理 (AJAX)
 */
function initAsyncImageDelete() {
    $(document).on('click', '.btn-delete-image', function () {
        const $btn = $(this);
        const url = $btn.data('url');
        const field = $btn.data('field');
        const $actionGroup = $btn.closest('.input-group-append');

        showAlert('warning', '刪除確認', '確定要移除此欄位的圖片嗎？', false, 'center', true, '確定刪除', 0, {
            showCancelButton: true,
            cancelButtonText: '取消',
            preConfirm: () => {
                // 執行真正的 AJAX 刪除
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                return $.post(url, { field: field })
                    .done(function (res) {
                        if (res.success) {
                            $actionGroup.addClass('d-none');
                            $(`#${field}`).val('');
                            $(`#stats-${field}`).empty();
                            showAlert('success', '已刪除', '圖片已移除', true, 'top-end', false, '', 2000);
                        }
                    })
                    .fail(function (err) {
                        const msg = err.responseJSON ? err.responseJSON.message : '系統錯誤';
                        showAlert('error', '失敗', msg, true, 'top-end');
                        $btn.prop('disabled', false).text('刪除');
                    });
            }
        });
    });
}

/**
 * 5. 上傳檔案資訊顯示
 * 用途：選取檔案後即時顯示檔名與大小
 */
function initImageUploadStats() {
    $(document).on('change', '.image-upload-input', function () {
        const file = this.files[0];
        if (!file) return;

        const fieldId = this.id;
        const $stats = $(`#stats-${fieldId}`);
        const $actionGroup = $(this).closest('.input-group').find('.input-group-append');
        const kb = (file.size / 1024).toFixed(2);

        $stats.html(`<i class="fas fa-check-circle text-success"></i> 已選：${file.name} (${kb} KB)`);
        $actionGroup.addClass('d-none'); // 隱藏舊的預覽按鈕防止混淆
    });
}

/**
 * 6. 狀態開關切換 (Boolean Switch)
 */
function handleToggleBooleanSwitch() {
    $(document).on("change", ".toggle-boolean-switch", function () {
        const $el = $(this);
        const data = {
            id: $el.data("id"),
            field: $el.data("field"),
            model: $el.data("model"),
            value: $el.is(":checked") ? 1 : 0
        };

        $.post(toggleBooleanUrl, data)
            .done(res => showAlert("success", "成功", res.message))
            .fail(xhr => {
                showAlert("error", "錯誤", xhr.responseJSON?.message || "更新失敗");
                $el.prop("checked", !data.value); // 失敗則彈回原狀
            });
    });
}

// 自訂 alert 顯示函式，顯示在正中間上方
/**
 * 顯示 SweetAlert2 提示視窗
 * 支援快速參數與完整 options 自訂
 *
 * @param {string} type - 圖標類型，預設 'info'
 *                        可選值：
 *                        'success'  => 成功
 *                        'error'    => 錯誤
 *                        'warning'  => 警告
 *                        'info'     => 資訊
 *                        'question' => 提問
 * @param {string} title - 標題文字，預設 ''
 * @param {string} message - 訊息內容，可支援 HTML，預設 ''
 * @param {boolean} toast - 是否啟用小型 toast 模式，預設 false
 * @param {string} position - 彈窗位置，預設 'center'
 *                            可選值：
 *                            'top', 'top-start', 'top-end',
 *                            'center', 'center-start', 'center-end',
 *                            'bottom', 'bottom-start', 'bottom-end'
 * @param {boolean} showConfirmButton - 是否顯示確認按鈕，預設 true
 * @param {string} confirmButtonText - 確認按鈕文字，預設 '確定'
 * @param {number} timer - 自動關閉時間（毫秒），預設 0 表示不自動關閉
 * @param {Object} options - 可傳入 SweetAlert2 完整設定參數覆蓋預設值，包括：
 *                           icon                : 圖標類型 ('success', 'error', 'warning', 'info', 'question')
 *                           title               : 標題文字
 *                           html                : HTML 內容文字
 *                           text                : 純文字訊息，若 html 設定則 text 被忽略
 *                           footer              : 底部文字
 *                           toast               : 是否啟用 toast 模式
 *                           position            : 彈窗位置 (同 type 參數)
 *                           showConfirmButton   : 是否顯示確認按鈕
 *                           confirmButtonText   : 確認按鈕文字
 *                           showCancelButton    : 是否顯示取消按鈕
 *                           cancelButtonText    : 取消按鈕文字
 *                           buttonsStyling      : 是否使用 SweetAlert2 內建按鈕樣式
 *                           timer               : 自動關閉時間 (毫秒)
 *                           timerProgressBar    : 是否顯示計時進度條
 *                           allowOutsideClick   : 點擊彈窗外部是否關閉
 *                           allowEscapeKey      : 按 Esc 是否關閉
 *                           allowEnterKey       : 按 Enter 是否觸發 confirm
 *                           stopKeydownPropagation : 阻止鍵盤事件冒泡
 *                           backdrop            : 是否顯示遮罩，可設為 true/false 或 'rgba(...)'
 *                           width               : 彈窗寬度，支援 px/%/auto
 *                           padding             : 內距
 *                           grow                : 自動調整尺寸模式，可選 'row', 'column', 'fullscreen'
 *                           customClass         : 自訂 CSS 類別
 *                               container
 *                               popup
 *                               header
 *                               title
 *                               closeButton
 *                               icon
 *                               image
 *                               content
 *                               input
 *                               actions
 *                               confirmButton
 *                               cancelButton
 *                               footer
 *                           imageUrl            : 彈窗顯示圖片 URL
 *                           imageWidth          : 圖片寬度
 *                           imageHeight         : 圖片高度
 *                           imageAlt            : 圖片 alt 屬性
 *                           didOpen             : 彈窗打開時回呼函式
 *                           willClose           : 彈窗關閉前回呼函式
 *                           didDestroy          : 彈窗完全移除後回呼函式
 *                           preConfirm          : 點 confirm 後執行，可返回 Promise
 *                           preDeny             : 點 deny 後執行
 *                           input               : 輸入框類型 ('text','email','number','password','textarea','select','radio','checkbox','file')
 *                           inputValue          : 預設輸入值
 *                           inputOptions        : select/radio 選項
 *                           inputPlaceholder    : 輸入框 placeholder
 *                           inputAttributes     : input 額外屬性
 *                           showLoaderOnConfirm : 點 confirm 是否顯示載入動畫
 *                           progressSteps       : 多步驟彈窗步驟陣列
 *                           currentProgressStep : 當前步驟索引
 */
function showAlert(
    type = "info", // 顯示圖標，success 或 error
    title = "", // 顯示標題
    message = "", // 顯示訊息
    toast = true, // 啟用 Toast 模式 (右上角彈出)
    position = "center", // 彈出位置
    showConfirmButton = false, // 是否顯示確認按鈕
    confirmButtonText = "確定",
    timer = 3000, // 3 秒後自動關閉
    options = {}
) {
    const defaults = {
        icon: type,
        title: title,
        html: message,
        text: "",
        footer: "",
        toast: toast,
        position: position,
        showConfirmButton: showConfirmButton,
        confirmButtonText: confirmButtonText,
        showCancelButton: false,
        cancelButtonText: "取消",
        confirmButtonColor: "#3085d6",
        buttonsStyling: true,
        timer: timer,
        timerProgressBar: false,
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        stopKeydownPropagation: false,
        backdrop: true,
        width: "auto",
        padding: "1.25rem",
        color: "#fff",
        background: "#1F2226",
        // theme: 'dark',
        grow: false,
        customClass: {
            container: "",
            popup: "border border-white rounded",
            header: "",
            title: "text-white",
            closeButton: "",
            icon: "",
            image: "",
            content: "",
            input: "",
            actions: "",
            confirmButton: "",
            cancelButton: "",
            footer: "",
        },
        imageUrl: "",
        imageWidth: "",
        imageHeight: "",
        imageAlt: "",
        didOpen: null,
        willClose: null,
        didDestroy: null,
        preConfirm: null,
        preDeny: null,
        input: null,
        inputValue: "",
        inputOptions: {},
        inputPlaceholder: "",
        inputAttributes: {},
        showLoaderOnConfirm: false,
        progressSteps: [],
        currentProgressStep: 0,
    };

    const settings = Object.assign({}, defaults, options);
    Swal.fire(settings);
}

/**
 * validateRequiredFields
 * 功能：檢查表單中所有帶有 'required-field' class 的欄位是否填寫
 * @param {string|object} formSelector - form 的 CSS 選擇器或 jQuery 物件
 * @returns {boolean} - 如果所有必填欄位都有值回傳 true，否則回傳 false
 */
function validateRequiredFields(formSelector) {
    let isValid = true; // 假設表單有效
    let firstInvalid = null; // 紀錄第一個未填欄位
    let missingFields = []; // 記錄未填欄位的提示訊息

    $(formSelector)
        .find(".required-field")
        .each(function () {
            let $field = $(this);
            let value = $field.val();
            // 取得欄位顯示名稱（依優先順序）
            // 1. data-label（最穩定，推薦）
            // 2. 對應的 <label> 文字
            // 3. name 屬性
            // 4. 預設「欄位」
            let label =
                $field.data("label") ||
                (function () {
                    // 嘗試抓同一個 form-group 裡的 label
                    let $label = $field
                        .closest(".form-group")
                        .find("label")
                        .first();
                    return $label.length ? $label.text().trim() : null;
                })() ||
                $field.attr("name") ||
                "欄位";

            if ($field.is(":checkbox") || $field.is(":radio")) {
                // 若 checkbox/radio 沒有被勾選
                if (!$field.is(":checked")) {
                    isValid = false;
                    firstInvalid = firstInvalid || $field;
                    missingFields.push(label + " 未勾選");
                    $field.addClass("is-invalid");
                } else {
                    $field.removeClass("is-invalid");
                }
            } else if ($field.is("select")) {
                // select 未選擇有效值
                if (!value || value === "") {
                    isValid = false;
                    firstInvalid = firstInvalid || $field;
                    missingFields.push(label + " 未選擇");
                    $field.addClass("is-invalid");
                } else {
                    $field.removeClass("is-invalid");
                }
            } else {
                // input / textarea
                if (!value || value.trim() === "") {
                    isValid = false;
                    firstInvalid = firstInvalid || $field;
                    missingFields.push(label + " 未填寫");
                    $field.addClass("is-invalid");
                } else {
                    $field.removeClass("is-invalid");
                }
            }
        });

    // 若有錯誤欄位
    if (!isValid && firstInvalid) {
        // 滾動到第一個錯誤欄位
        $("html, body").animate(
            {
                scrollTop: firstInvalid.offset().top - 100,
            },
            300
        );
        firstInvalid.focus();

        // 將缺失欄位格式化成有序列表
        const fieldList = missingFields.map((f) => `<li>${f}</li>`).join("");

        showAlert(
            "warning", // type
            "欄位未填寫", // title
            `<ol>${fieldList}</ol>`, // message，使用有序列表 <ol>
            true, // toast
            "bottom", // position
            false, // showConfirmButton
            "確定", // confirmButtonText
            5000, // timer (0 = 不自動關閉)
            {
                customClass: {
                    title: "text-warning", // 使用 Bootstrap 標題樣式
                },
            }
        );
    }

    return isValid;
}

/**
 * syncSummernoteContent
 * 功能：將 Summernote 內容同步回 textarea
 * 只在特定表單頁面使用，不與 validateRequiredFields 綁定
 */
function syncSummernoteContent(formSelector) {
    $(formSelector)
        .find(".summernote")
        .each(function () {
            const content = $(this).summernote("code");
            $(this).val(content);
        });
}

/**
 * 顯示 SweetAlert2 刪除確認視窗（共用）
 *
 * @param {number} id - 要刪除的資料 ID
 * @param {string} title - 提示標題（由各管理頁決定）
 * @param {string} text - 提示內容
 */
function confirmDelete(id, title, text) {
    showAlert(
        "warning", // 警告類型
        title, // ← 不再寫死
        text, // ← 不再寫死
        false, // Toast 關閉
        "center",
        true,
        "刪除",
        0,
        {
            showCancelButton: true,
            cancelButtonText: "取消",

            // 確認後才真的刪除
            preConfirm: function () {
                const form = document.getElementById("deleteForm" + id);

                if (!form) {
                    console.error("找不到刪除表單：deleteForm：" + id);
                    return false; // 防呆
                }

                form.submit();
            },

            customClass: {
                title: "text-white",
                popup: "border border-white rounded",
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-secondary",
            },
        }
    );
}
