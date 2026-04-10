// 設定 jQuery AJAX 全域 Header，這樣就不用在每個 data 裡傳 _token 了
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(function () {
    console.log('Admin common.js loaded');

    // --- 全域導覽/操作 (各頁面通用) ---
    initQuickClear();           // 快速清除快取 (通常在 Navbar)

    // --- 列表/瀏覽頁邏輯 (當存在相關按鈕時執行) ---
    if ($('.js-delete-btn, .row-checkbox, .btn-toggle-tree, .js-open-preview').length > 0) {
        initGlobalDelete();     // 單筆刪除
        initTreeToggle();       // 樹狀摺疊
        initBatchDelete();      // 批次刪除
        initImagePreview();     // 圖片預覽
    }

    // --- 表單/編輯頁邏輯 (當存在表單時執行) ---
    if ($('form').length > 0) {
        initAsyncImageDelete();     // 異步刪除圖片
        initImageUploadStats();     // 上傳資訊更新
        handleToggleBooleanSwitch();// 開關切換
        initTagsInput();            // 標籤輸入
        initRangeSlider();          // 滑桿輸入
        initGlobalFormSubmit();     //
    }
});


/**
 * 初始化「快速清除系統快取」功能
 * 考慮到 showAlert 封裝函式不回傳 Promise 的限制
 * 直接在 preConfirm 邏輯中處理執行成功後的提示與狀態恢復
 * * @param void
 */
function initQuickClear() {
    // 透過事件委派監聽點擊，避免動態生成的按鈕失效
    $(document).on('click', '#btn-quick-clear', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const apiUrl = $btn.data('url') || '/admin/clear-cache';

        // 直接呼叫 showAlert 啟動詢問視窗
        showAlert(
            'question',      // 提問類型圖標
            '系統快取清除',    // 標題
            '確定要清除所有系統快取嗎？', // 訊息
            false,           // 這裡是確認視窗，不使用 Toast
            'center',        // 置中顯示
            true,            // 顯示確認按鈕
            '確定清除',       // 確認按鈕文字
            0,               // 不要自動關閉，等使用者點擊
            {
                showCancelButton: true,
                cancelButtonText: '取消',
                showLoaderOnConfirm: true, // 開啟 Swal 內建確認按鈕的 Loading 狀態

                // 核心邏輯：在點擊確認後直接處理所有後續動作
                preConfirm: () => {
                    // 同步處理：讓頁面上的實體按鈕也旋轉，增強視覺回饋
                    $btn.prop('disabled', true).find('i').addClass('fa-spin');

                    // 返回 jQuery 的 AJAX 物件 (這本身就是一個類 Promise)
                    return $.post(apiUrl)
                        .done(function(res) {
                            // 成功時：因為外層 .then 無法運作，直接在此呼叫成功提示
                            showAlert(
                                'success',
                                '清除成功',
                                res.message || '系統快取已清除成功！',
                                true,      // 成功用 Toast 模式
                                'center',
                                false,
                                '',
                                2000
                            );
                        })
                        .fail(function(xhr) {
                            // 失敗時：顯示錯誤訊息在原本的彈窗內
                            const errorMsg = xhr.responseJSON?.message || '系統執行失敗';
                            Swal.showValidationMessage(`錯誤：${errorMsg}`);
                        })
                        .always(function() {
                            // 無論成功或失敗，務必恢復實體按鈕的狀態與動畫
                            $btn.prop('disabled', false).find('i').removeClass('fa-spin');
                        });
                },
                allowOutsideClick: () => !Swal.isLoading() // 防止在處理中因點擊背景而中斷請求
            }
        );
    });
}

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
    $(document).on('change', '.row-checkbox', updateBtnState);

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
        $(this).closest('.input-group').find('.input-group-append').addClass('d-none'); // 隱藏舊的預覽按鈕防止混淆
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
 * 用途：將 Summernote 編輯器的內容同步回原始的 HTML textarea 欄位
 * 理由：因為 Summernote 會隱藏原始欄位，如果不手動同步，Laravel Request 會抓到空的內容
 * @param {string} formSelector - 表單的選擇器，例如 'form[name="the-form"]'
 */
function syncSummernoteContent(formSelector) {
    // 透過選擇器找到該表單
    var $targetForm = $(formSelector);

    // 如果找不到表單，直接結束（防呆）
    if ($targetForm.length === 0) return;

    // 尋找表單內所有帶有 .summernote 類別的元素並逐一處理
    $targetForm.find(".summernote").each(function () {
        // 取得該編輯器的 HTML 代碼
        var content = $(this).summernote("code");
        // 將內容塞回原始的 textarea
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

/**
 * 原生 Tags Input (支援排序)
 */
function initTagsInput() {
    const wrappers = document.querySelectorAll('.js-tags-input');
    if (wrappers.length === 0) return; // 沒找到就直接跳出

    wrappers.forEach(wrapper => {

        const name = wrapper.dataset.name;
        const placeholder = wrapper.dataset.placeholder || 'Enter...';

        wrapper.classList.add('tags-container');

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = placeholder;

        wrapper.appendChild(input);

        // 將原本 span 轉為正式 tag
        wrapper.querySelectorAll('.tag-item').forEach(el => {
            createTag(el.dataset.value);
            el.remove();
        });

        function createTag(value) {
            if (!value.trim()) return;

            // 避免重複
            if ([...wrapper.querySelectorAll('.tag')]
                .some(tag => tag.dataset.value === value)) return;

            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.draggable = true;
            tag.dataset.value = value;
            tag.innerHTML = `
                ${value}
                <button type="button">&times;</button>
            `;

            // hidden input
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = value;

            tag.appendChild(hidden);
            wrapper.insertBefore(tag, input);

            // 刪除
            tag.querySelector('button').addEventListener('click', () => {
                tag.remove();
            });

            // 拖曳
            tag.addEventListener('dragstart', e => {
                e.dataTransfer.setData('text/plain', value);
                tag.classList.add('dragging');
            });

            tag.addEventListener('dragend', () => {
                tag.classList.remove('dragging');
            });
        }

        // Enter 新增
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault(); // 確保這行有確實阻斷
                e.stopPropagation(); // 增加這行防止冒泡到 Form
                createTag(input.value.trim());
                input.value = '';
            }
        });

        // 點容器聚焦
        wrapper.addEventListener('click', () => input.focus());

        // 排序邏輯
        wrapper.addEventListener('dragover', e => {
            e.preventDefault();
            const dragging = wrapper.querySelector('.dragging');
            const afterElement = [...wrapper.querySelectorAll('.tag:not(.dragging)')]
                .find(tag => e.clientX <= tag.getBoundingClientRect().left + tag.offsetWidth / 2);
            if (afterElement) {
                wrapper.insertBefore(dragging, afterElement);
            } else {
                wrapper.insertBefore(dragging, input);
            }
        });

    });
}

/**
 * 初始化 Slider 顯示數值
 */
function initRangeSlider() {

    const wrappers = document.querySelectorAll('.js-range-input');
    if (wrappers.length === 0) return; // 沒找到就直接跳出

    wrappers.forEach(range => {

        const valueEl = range
            .closest('.slider-wrapper')
            .querySelector('.js-range-value');

        // 初始化顯示
        valueEl.textContent = range.value;

        // 即時更新
        range.addEventListener('input', function () {
            valueEl.textContent = this.value;
        });
    });
}

/**
 * 全域表單提交防呆
 * 用途：自動監聽所有表單提交，並將內部的 .js-submit-btn 轉為讀取狀態
 */
function initGlobalFormSubmit() {
    // 監聽 document 下所有包含 .js-submit-btn 的表單提交事件
    $(document).on('submit', 'form', function () {
        const $form = $(this);
        const $btn = $form.find('.js-submit-btn');

        // 如果表單內沒有標記 .js-submit-btn，則不執行（保持一般表單彈性）
        if ($btn.length === 0) return;

        // 如果已經在載入中，則不重複執行（防呆）
        if ($btn.prop('disabled')) return false;

        // 設定為讀取狀態
        setGlobalLoading($btn, true);
    });
}

/**
 * 設定按鈕讀取狀態 (全域工具函式)
 * @param {jQuery} $el - 按鈕元素
 * @param {boolean} isLoading - 是否載入中
 */
function setGlobalLoading($el, isLoading) {
    if (isLoading) {
        // 儲存原始 HTML 到 data 屬性中，方便稍後還原
        if (!$el.data('original-html')) {
            $el.data('original-html', $el.html());
        }

        $el.prop('disabled', true);

        // 判斷按鈕文字，讓 UI 更有人情味 (例如刪除按鈕顯示「刪除中...」)
        const loadingText = $el.data('loading-text') || '處理中...';
        $el.html(`<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`);
    } else {
        $el.prop('disabled', false);
        const originalHtml = $el.data('original-html');
        if (originalHtml) {
            $el.html(originalHtml);
        }
    }
}
