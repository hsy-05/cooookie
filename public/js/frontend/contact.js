/**
 * 聯絡我們頁面整合模組
 * 整合功能：AOS 動畫刷新、圖形驗證碼切換、Google reCAPTCHA v3 驗證、表單異步提交
 */
const ContactModule = (() => {

    // 常用 DOM 節點快取，減少重複查詢消耗效能
    const nodes = {
        form: document.querySelector('#form_contact'),
        submitBtn: document.querySelector('#btn-submit'),
        captchaImg: document.querySelector('#captcha-img'),
        refreshBtn: document.querySelector('#refresh-captcha'),
        recaptchaInput: document.querySelector('#recaptcha_token')
    };

    /**
     * 強制刷新 AOS 狀態
     * 確保在所有資源（尤其是 CSS）加載後正確計算元素位置，避免動畫卡死
     */
    const refreshAOS = () => {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 1000,
                once: true,
                offset: 50,
                delay: 100
            });
            // 手動觸發刷新，重新計算頁面滾動位置
            AOS.refresh();
        }
    };

    /**
     * 獲取新的圖形驗證碼
     * 透過加掛時間戳記，強制瀏覽器繞過緩存重新載入圖片
     * @param {HTMLImageElement} img - 驗證碼圖片的 DOM 物件
     */
    const getNewCaptcha = (img) => {
        if (!img) return;
        const srcUrl = img.src.split('?')[0];
        img.src = `${srcUrl}?t=${new Date().getTime()}`;
    };

    /**
     * 取得 Google reCAPTCHA v3 Token
     * 每次送出表單前都重新獲取，確保 Token 不會因逾時而失效
     * @returns {Promise<string|null>} 返回驗證 Token 或 null
     */
    const getRecaptchaToken = async () => {
        try {
            // 檢查全域環境是否有載入 Google API
            if (typeof grecaptcha === 'undefined') {
                throw new Error('Google 驗證服務載入失敗，請檢查網路連線');
            }

            // 等待 reCAPTCHA 載入完成並執行驗證
            return await new Promise((resolve, reject) => {
                grecaptcha.ready(async () => {
                    try {
                        // 這裡的 Site Key 是從 Blade 傳遞過來的全域變數或配置
                        const siteKey = document.querySelector('meta[name="recaptcha-key"]')?.content;
                        if (!siteKey) return reject('找不到 reCAPTCHA Site Key');

                        const token = await grecaptcha.execute(siteKey, { action: 'contact_form' });
                        resolve(token);
                    } catch (e) {
                        reject(e);
                    }
                });
            });
        } catch (err) {
            console.error('reCAPTCHA Error:', err);
            return null;
        }
    };

    /**
     * 表單提交事件主處理邏輯
     * 包含前端欄位驗證、Google 安全驗證、後端異步請求
     * @param {Event} e - 瀏覽器事件物件
     */
    const onFormSubmit = async (e) => {
        e.preventDefault();

        // 基礎前端 HTML5 欄位規則驗證 (required, email 格式等)
        if (!nodes.form.checkValidity()) {
            nodes.form.reportValidity();
            return;
        }

        // 進入提交狀態：鎖定按鈕防止重複點擊
        nodes.submitBtn.disabled = true;
        const btnOriginalText = nodes.submitBtn.innerText;
        nodes.submitBtn.innerText = '安全驗證中...';

        try {
            // 執行 Google 隱形驗證獲取 Token
            const token = await getRecaptchaToken();
            if (!token) {
                throw new Error('安全驗證失敗，請重新整理頁面再試一次');
            }

            // 將 Token 填入隱藏欄位供後端校驗
            nodes.recaptchaInput.value = token;
            nodes.submitBtn.innerText = '傳送中...';

            const formData = new FormData(nodes.form);

            // 發送 AJAX 請求至伺服器
            const response = await fetch('/contact/store', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '發送成功',
                    text: '我們已收到您的諮詢，會儘速與您聯繫。',
                    confirmButtonColor: '#111'
                });
                nodes.form.reset();
            } else {
                // 如果後端回傳 success: false，拋出錯誤訊息
                throw new Error(data.message || '請確認輸入資訊是否正確');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: '傳送失敗',
                text: err.message
            });
        } finally {
            // 回復按鈕狀態並更新圖形驗證碼
            nodes.submitBtn.disabled = false;
            nodes.submitBtn.innerText = btnOriginalText;
            getNewCaptcha(nodes.captchaImg);
        }
    };

    /**
     * 模組初始化啟動器
     * 負責掛載所有監聽事件
     */
    const init = () => {
        // 表單提交監聽
        if (nodes.form) {
            nodes.form.addEventListener('submit', onFormSubmit);
        }

        // 圖形驗證碼手動刷新
        if (nodes.refreshBtn) {
            nodes.refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                getNewCaptcha(nodes.captchaImg);
            });
        }

        // 當頁面資源完整加載後，啟動並刷新 AOS 動畫
        window.addEventListener('load', refreshAOS);
    };

    // 暴露 boot 接口供外部呼叫
    return { boot: init };
})();

// 當 DOM 樹準備完成後執行初始化
document.addEventListener('DOMContentLoaded', ContactModule.boot);
