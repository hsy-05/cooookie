/**
 * 聯絡我們頁面整合模組
 * 整合功能：AOS 動畫刷新、Google reCAPTCHA v3 驗證安全標記、表單異步提交
 */
const ContactModule = (() => {

    // 常用 DOM 節點快取，減少重複查詢消耗效能
    const nodes = {
        form: document.querySelector('#form_contact'),
        submitBtn: document.querySelector('#btn-submit'),
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
     * 取得 Google reCAPTCHA v3 Token
     * 每次送出表單前都重新獲取，確保 Token 不會因逾時而失效
     * @returns {Promise<string|null>} 返回驗證 Token 或 null
     */
    const getRecaptchaToken = async () => {
        try {
            if (typeof grecaptcha === 'undefined') {
                throw new Error('Google 驗證服務載入失敗，請檢查網路連線');
            }

            return await new Promise((resolve, reject) => {
                grecaptcha.ready(async () => {
                    try {
                        // 從表單節點的 data-site-key 屬性中動態獲取網站金鑰
                        const siteKey = nodes.form?.getAttribute('data-site-key');
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

        if (!nodes.form.checkValidity()) {
            nodes.form.reportValidity();
            return;
        }

        // 讀取由後端 Blade 輸出的實體提交網址，確保路徑在任何部署環境下皆自動對齊
        const submitUrl = nodes.form.getAttribute('data-action');
        if (!submitUrl) {
            Swal.fire({ icon: 'error', title: '系統設定錯誤', text: '找不到表單提交路徑。' });
            return;
        }

        nodes.submitBtn.disabled = true;
        const btnOriginalText = nodes.submitBtn.innerText;
        nodes.submitBtn.innerText = '安全驗證中...';

        try {
            const token = await getRecaptchaToken();
            if (!token) {
                throw new Error('安全驗證失敗，請重新整理頁面再試一次');
            }

            nodes.recaptchaInput.value = token;
            nodes.submitBtn.innerText = '傳送中...';

            const formData = new FormData(nodes.form);

            // 發送 AJAX 請求至動態配置的伺服器接收端點
            const response = await fetch(submitUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    // 防呆核心：明確告訴 Laravel，我只要 JSON 格式的回應，不准吐 HTML 網頁給我
                    // 這樣就算後端驗證（ContactRequest）失敗，Laravel 也會乖乖回傳 422 錯誤原因 JSON，而不會吐網頁
                    'Accept': 'application/json'
                }
            });

            // 讀取後端解析後的 JSON 資料
            const data = await response.json();

            // 如果後端傳回非 200 到 299 的狀態碼（例如 422 欄位驗證失敗、500 伺服器出錯）
            if (!response.ok) {
                // 如果是 Laravel FormRequest 的欄位驗證錯誤，錯誤訊息通常包在 data.errors 裡面
                if (response.status === 422 && data.errors) {
                    // 把所有欄位的錯誤原因組合成一段文字交代清楚
                    let errorMessages = Object.values(data.errors).flat().join('\n');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || `伺服器回應錯誤 (${response.status})`);
            }

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '發送成功',
                    text: '我們已收到您的諮詢，會儘速與您聯繫。',
                    confirmButtonColor: '#111'
                });
                nodes.form.reset();
            } else {
                throw new Error(data.message || '請確認輸入資訊是否正確');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: '傳送失敗',
                text: err.message
            });
        } finally {
            nodes.submitBtn.disabled = false;
            nodes.submitBtn.innerText = btnOriginalText;
        }
    };

    /**
     * 模組初始化啟動器
     * 負責掛載所有監聽事件
     */
    const init = () => {
        if (nodes.form) {
            nodes.form.addEventListener('submit', onFormSubmit);
        }
        window.addEventListener('load', refreshAOS);
    };

    return { boot: init };
})();

document.addEventListener('DOMContentLoaded', ContactModule.boot);
