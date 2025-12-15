$(function () {
    // 監聽所有帶有 'toggle-boolean-switch' 類別的 checkbox 的 change 事件
    handleToggleBooleanSwitch();

    // ❌ 不自動綁定表單 submit 事件
    // ❌ 不自動同步 Summernote
    // 這些由各個表單頁面決定是否要綁定
});

// 監聽 'toggle-boolean-switch' checkbox 變化並發送 AJAX
function handleToggleBooleanSwitch() {
    $(".toggle-boolean-switch").on("change", function () {
        const switchElement = $(this);
        const id = switchElement.data("id");
        const field = switchElement.data("field");
        const model = switchElement.data("model");
        const value = switchElement.is(":checked") ? 1 : 0;

        $.ajax({
            url: toggleBooleanUrl,
            method: "POST",
            data: {
                _token: csrfToken,
                model: model,
                id: id,
                field: field,
                value: value,
            },
            success: function (response) {
                showAlert("success", "成功", response.message);
            },
            error: function (xhr) {
                let errorMessage = "狀態更新失敗。";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert("error", "錯誤", errorMessage);
                switchElement.prop("checked", !value);
            },
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
    position = "top-end", // 彈出位置
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
        background: "#6C757D",
        // theme: 'dark',
        grow: false,
        customClass: {
            container: "",
            popup: "",
            header: "",
            title: "",
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
            let label = $field.data("label") || $field.attr("name") || "欄位"; // 用 data-label 或 name

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
                    missingFields.push(label);
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
