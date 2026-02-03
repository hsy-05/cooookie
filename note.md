# 🍪 【COOOOKIE】專案開發維護筆記

本文件紀錄專案核心架構與功能邏輯，方便新手快速上手與面試說明。

---

# 架構設計核心原則

1. Controller 只負責請求與回應
2. 業務邏輯集中於 Service Layer
3. 共用行為以 Event / Listener 與 Trait 實現
4. 避免萬用 Helper，確保責任單一

---

## 📂 1. 資料夾分工與用途 (為什麼要這樣分？)

### 🛡️ Middleware (警衛室)

**用途：** 在進入頁面「前」做檢查或過濾。

- **CheckBackendPermission.php**: 檢查管理員有沒有「權限」。沒權限就攔截並彈出提示。
- **VerifyCsrfToken.php**: 設定 `ckfinder/*` 排除檢查，避免編輯器上傳圖片被攔截。
- **EncryptCookies.php**: 排除 CKFinder 的 Cookie 加密，確保外部套件讀得到通行證。

### 🛠️ Helpers (小工具箱)

**用途：** 存放全站通用的「靜態小工具」。

- **ContentHelper.php**: 處理文字。把網址換成標記 `[[SITE_URL]]`（防破圖）及顯示跳窗訊息。
- **ImageHelper.php**: 處理圖片。縮圖、裁切、自動生成檔名、批次刪除檔案。
- **SummernoteImageHelper.php**: 清理垃圾。刪除編輯器內容時，自動把沒用的圖片從硬碟刪除。

### 🔔 Listeners & Traits (自動化助手)

**用途：** 處理「自動觸發」的行為。

- **Listeners/LogSuccessfulLogin.php**: 只要有人「登入成功」，就會自動在資料庫記下一筆時間與 IP。
- **Traits/Loggable.php**: 一個共用模組。讓不同的功能（如消息、產品）只要「掛載」它，就能輕鬆寫入「新增/編輯/刪除」的操作紀錄。

### ⚙️ Providers (系統初始化)

**用途：** 網站啟動時的「總開關」。

- **AppServiceProvider.php**: 設定全域變數（如 `BASE_URL`）給所有頁面用，並定義權限閘門（Gate）。
- **RouteServiceProvider.php**: 設定登入後的「預設家目錄」為 `/admin`。

---

## 🔑 2. 核心功能調整指引

### A. 權限管理 (BaseAdminController)

**路徑：** `app/Http/Controllers/Admin/BaseAdminController.php`

- **邏輯：** 只要在子類別設定 `protected $permissionName = 'news';`，系統會自動檢查 `.view`, `.create`, `.delete` 權限。
- **面試回答：** 「這叫『約定優於配置』，能減少重複寫檢查代碼的時間，提高維護效率。」

### B. 沒權限時的攔截跳轉

**路徑：** `app/Http/Middleware/CheckBackendPermission.php`

- **邏輯：** 當 `canDo` 檢查失敗，呼叫 `ContentHelper::showMsg` 存入訊息，並 `redirect()` 回前一頁。

### C. 檔案與圖片安全 (CSRF/Cookie)

**路徑：** `VerifyCsrfToken.php` & `EncryptCookies.php`

- **邏輯：** 為了讓編輯器套件 (CKFinder) 運作，我們必須將其路徑設為「例外排除」。

### D. 渲染畫面請用 $this->view 
- **檔案：** `BaseAdminController.php`
- **說明：** - ❌ 禁止使用全域 `view()`：會遺失父類別預設好的變數。
  - ✅ 必須使用 `$this->view()`：它會自動掛載後台所需的變數（如 PageTitle、URL 等）。
  - **用途：** 確保所有後台頁面風格統一，且減少重複代碼。

---

---
## 📝 程式碼邏輯筆記 (新手重點)

### 1. 常用關鍵字
- **$this**: 指向「當前物件」。常用於 `$this->view()`，會自動從自己或父類別找功能。
- **parent::**: 指向「父類別」。常用於 `parent::__construct()`，確保老爸的初始化邏輯有執行。
- **::class**: 取得「完整路徑名稱」。例如 `User::class` 會變成 `"App\Models\User"`，安全且防呆。
- **$event**: 代表「事件包裹」。在 Listener 中用來拆開看裡面發生了什麼事。

### 2. $this->view 運作原理
- **尋找路徑**：NewsController (無) -> BaseAdminController (有!)。
- **好處**：父類別已經把 URL、權限、標題都包好了，子類別直接用即可，這就是「封裝」的力量。

---

## 💡 3. 新手 FAQ

**Q1：為什麼要把邏輯從 Controller 抽出來放到 Helper 或 Service？**

> **答：** 「為了讓 Controller 保持乾淨。Controller 只負責接收請求，而具體的圖片處理或文字轉換邏輯放在 Helper，這樣不同的功能都可以共用同一個工具，未來要改圖案規格時，改一個地方全站就都改好了。」

**Q2：你的權限管理是怎麼設計的？**

> **答：** 「我用了一個基類 `BaseAdminController` 來自動化處理。透過自定義的 Middleware 攔截請求，並比對管理員的 Role 是否具備該權限。如果沒權限，我會利用 Session Flash 把錯誤訊息傳回原頁面彈出警告。」

**Q3：為什麼資料庫不直接存圖片網址，要換成 [[SITE_URL]]？**

> **答：** 「這是為了『搬家』做準備。如果未來網站從測試站搬到正式站（網域變了），資料庫內的網址標記會自動還原成當前的正確網域，不會發生圖片通通破圖的問題。」
