# 🍪 【COOOOKIE】專案開發維護筆記

本文件紀錄專案核心架構與功能邏輯，方便新手快速上手與面試說明。

---

# 🚀 架構設計核心原則

1. **Controller 只負責請求與回應**：不寫複雜邏輯。
2. **業務邏輯集中於 Service Layer / Helper**：提高重複使用率。
3. **共用行為以 Event / Listener 與 Trait 實現**：例如自動記錄 Log、自動刪除圖片。
4. **避免萬用 Helper**：確保每個工具類別責任單一（例如：圖片歸圖片、文字歸文字）。

---

## 📂 1. 資料夾分工與用途 (為什麼要這樣分？)

### 🛡️ Middleware (警衛室)
**用途：** 在進入頁面「前」做檢查或過濾。
- `CheckBackendPermission.php`: 檢查管理員權限，沒權限就攔截並提示。
- `VerifyCsrfToken.php`: 排除編輯器相關路徑，避免上傳圖片時被系統擋掉。
- `EncryptCookies.php`: 確保外部套件（如檔案管理員）能正常讀取 Cookie。

### 🛠️ Helpers (小工具箱)
**用途：** 存放全站通用的「靜態小工具」，像專業設計公司的交接清單。
- `ContentHelper.php`: 處理文字，將 URL 轉換為 `[[SITE_URL]]` 標記，避免搬站破圖。
- `ImageHelper.php`: 處理圖片縮圖、裁切、自動生成唯一檔名。
- `SummernoteImageHelper.php`: **【核心】** 自動比對 HTML 內容，清理掉沒被使用的廢棄圖片。

### 🔔 Listeners & Traits (自動化助手)
**用途：** 處理「自動觸發」行為，讓程式碼更精簡。
- `LogSuccessfulLogin.php`: 登入成功自動記 IP。
- `Loggable.php`: 只要掛載這個 Trait，該資料表的「新增/編輯/刪除」就會自動寫入 Log。
- `HasImageFields.php`: 自動處理 Model 刪除時的實體檔案清理。

---

## 🔑 2. 核心功能調整指引

### A. 權限管理 (BaseAdminController)
- **邏輯：** 子類設定 `protected $permissionName = 'news';`，系統自動檢查權限。
- **面試回答：** 「使用基類繼承與約定優於配置的原則，大幅減少重複檢查代碼。」

### B. 渲染畫面 (專利寫法)
- ✅ 使用 `$this->view()` 而非全域 `view()`。
- **用途：** 父類別會自動掛載 `PageTitle`、`UserPermission` 等全域變數，保證後台風格一致。

---

## 📝 3. Summernote 圖片處理專項說明

這是本專案最細心的設計，解決了編輯器容易產生垃圾檔案的問題。

### 核心機制：內容比對法 (Content Sync)
當編輯器內容更新時，系統會執行以下流程：

[ 取得舊 HTML ] vs [ 取得新 HTML ]
        |               |
        v               v
 [ 提取舊圖片路徑 ]   [ 提取新圖片路徑 ]
        |               |
        \-------+-------/
                |
          [ 找出差集 ] (舊有新無的圖片)
                |
          [ 執行實體刪除 ] (Storage::delete)

### 關鍵防呆：Session 暫存追蹤
- **上傳時**：呼叫 `trackTempImage`，將圖片路徑暫存在 Session。
- **儲存後**：呼叫 `commitTempImages`，確認存檔，移除追蹤。
- **清理時**：`cleanAbandonedImages` 會自動掃除那些「傳了圖卻沒按存檔就跑掉」的垃圾檔案。

---

## 📊 4. 系統邏輯流程圖 (ASCII)

### A. Summernote 圖片存檔流程

[ 使用者按下儲存 ]
       |
       v
[ NewsController@update ]
       |
       v
[ saveTranslations() ]
       |
       |-- 1. `ContentHelper::decodeSiteUrl` (還原網址)
       |-- 2. `SummernoteImageHelper::syncEditorImages` (比對並刪除舊圖)
       |-- 3. `SummernoteImageHelper::commitTempImages` (結案暫存清單)
       |-- 4. `ContentHelper::encodeSiteUrl` (轉為 [[SITE_URL]] 儲存)
       v
[ 資料庫儲存成功 ]



### B. HasImageFields 圖片欄位刪除流程 (AJAX)


[ 使用者點擊網頁刪除鈕 ]
       |
       v
[ AJAX 請求 ] --> [ NewsController@deleteImageField ]
                        |
                        v
[ News Model ] <--- [ HasImageFields Trait ]
                        |
[ ImageHelper ] <--- [ removeImageFromField ]
       |
       v
[ 實體檔案刪除 ] --> [ 回傳 JSON 成功 ]



---

## 💡 5. 新手 FAQ (面試備備)

**Q1：為什麼不直接存完整網址 `https://...` 到資料庫？**

> **答：** 避免搬站或更換網域（SSL）時產生破圖。存 `[[SITE_URL]]` 可以在輸出時動態還原，這是專業公司的標準作法。

**Q2：如何防止使用者關掉瀏覽器導致的圖片堆積？**

> **答：** 我們設計了 `Session` 追蹤機制，當下次有人進入新增頁面時，系統會順手呼叫 `cleanAbandonedImages` 清理掉過期的暫存圖片。

**Q3：為什麼要把 Summernote 的功能封裝在 Helper？**

> **答：** 因為消息、產品、關於我們都會用到。封裝後，我只需要一行代碼 `syncEditorImages()` 就能處理所有圖片同步，易於維護且防呆。

---

## 🛠️ 6. 專案清單與角色設定

| 資料夾/檔案 | 目的 |
| --- | --- |
| `app/Helpers` | 存放不具備狀態的純邏輯工具 (如 Summernote 清理工具) |
| `app/Traits` | 存放可重複掛載的行為 (如 Loggable, HasImageFields) |
| `public/js/admin` | 後台 JS 集中地，如 `summernote-init.js` |

| 角色等級 | 權限範圍 | 說明 |
| --- | --- | --- |
| **L1 Developer** | `["system", "all"]` | 開發者專用，看得到系統設定 |
| **L2 Manager** | `["all"]` | 站長，可管理所有內容但不能改程式設定 |
| **L3 Admin** | `由 Role 決定` | 一般人員，僅能操作獲授權的模組 (如：消息管理) |
