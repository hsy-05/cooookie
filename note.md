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

**********************************************************************************
## 1. 系統整體邏輯說明
這套系統採用 「資料驅動介面 (Data-Driven UI)」 的邏輯：

Database: 儲存每一項設定的「元數據 (Metadata)」。type 決定前端長什麼樣子，config 決定該組件的細節參數（如 Radio 的選項、Slider 的最大最小值）。

Model: 利用 Laravel 的 Casts 功能，將 JSON 自動轉換成 PHP 陣列。並透過 Accessor 封裝解析邏輯。

Controller: 負責批次更新，並具備「合法性檢查」，只允許更新資料庫中已定義的 Key，防止非法注入。

Blade: 根據 type 切換對應的 HTML 結構，不處理任何字串解析。


**************************************************************
HasImageFields.php 觸發流程圖

[ 使用者點擊網頁按鈕 ]
       |
       v
[ AJAX 請求 (POST /admin/news/delete-image/15) ]
       |
       v
[ Laravel Route 路由中心 ] -- (配對到) --> [ NewsController@deleteImageField ]
                                              |
                                              | 呼叫
                                              v
[ HasImageFields Trait ] <--- (提供技能) --- [ News Model 物件 ]
       |
       | 執行 deleteImageFieldGeneric()
       | 裡面再執行 removeImageFromField()
       |
       v
[ 執行 ImageHelper 刪檔 ] ----> [ 回傳 JSON 結果給前端 ]


----- 流程圖：從網址到物件的誕生 ------
[ 瀏覽器 ] -> 發送請求: DELETE admin/news/delete-image/15
    |
    v
[ Route 路由 ] -> 發現參數 {news} = 15
    |
    v
[ Laravel 核心 ] -> 檢查 Controller 參數型別為 News
    |      |
    |      +--> 自動執行: News::where('news_id', 15)->firstOrFail()
    |             (如果沒這筆資料，直接回傳 404)
    v
[ NewsController ] -> 拿到已經裝滿資料的 $news 物件
    |
    +--> 執行 $news->deleteImageFieldGeneric($request)

========================================================
多語系設定

流程圖：後台編輯 vs. 系統設定
【後台編輯模式 (Form)】
getActiveLanguages() -> 拿到 [中, 英, 日]
      |
      v
Blade @foreach -> 產生三個輸入框
      |
      v
一次儲存所有語系到 NewsDesc 表

------------------------------------

【系統顯示模式 (Index/前台)】
SetLocale Middleware -> 確定現在是 "zh_TW" (ID: 1)
      |
      v
Model $news->title -> 自動去抓 lang_id = 1 的描述
      |
      v
畫面顯示：「中文標題」


===========================================================
===========================================================

## 權限與操作紀錄邏輯流程圖

管理者請求 (Request)
      |
      v
[ CheckBackendPermission ] <--- (中介層攔截)
      |
      +--- [ 否 ] ---> 顯示「權限不足」訊息 -> 跳回前一頁 (withInput)
      |
      +--- [ 是 ] ---> 進入 Controller (執行功能)
              |
              v
      [ User::canDo($perm) ] <--- (權限判斷核心)
              |
      /-------+-------\
      |       |       |
 [Developer] [Admin] [Role/User]
  (全部過)   (排除系統) (依權限清單)
              |
              v
      [ 執行資料庫操作 ] <--- (觸發自動化機制)
              |
      /-------+-------\
      |               |
 [ HasImageFields ]  [ Loggable ]
 (自動清理舊圖檔)    (手動/自動寫入日誌)
      |               |
      \-------+-------/
              |
              v
        [ ActionLog ] <--- (資料庫紀錄)
      (格式化顯示: 去除重複動作字眼)


===========================================================
===========================================================
## ===== 聯絡我們 相關檔案說明 =====

| 檔案路徑 | 類型 | 說明 |
| :--- | :--- | :--- |
| app/Helpers/MailConfigHelper.php | 工具類 | 負責動態修改 SMTP 設定，解決 TLS 憑證報錯問題。 |
| app/Http/Controllers/Admin/ContactController.php | 後台控制 | 處理列表顯示、讀取狀態更新、回覆儲存與寄信邏輯。 |
| app/Http/Controllers/Frontend/ContactController.php | 前台控制 | 處理表單顯示、資料存檔、通知管理員的邏輯。 |
| app/Mail/ContactNotification.php | 郵件物件 | 定義郵件的主旨與指定對應的 Blade 樣板。 |
| app/Models/Contact.php | 模型 | 定義聯絡單主表，包含與回覆表的一對多關聯。 |
| app/Models/ContactReply.php | 模型 | 定義回覆紀錄表，關聯回主表。 |
| database/seeders/MailSettingsSeeder.php | 資料填充 | 初始設定 SMTP 所需的資料庫欄位與預設值。 |

<pre>

[前台使用者] ---- 填寫表單 ----> [Frontend ContactController@store]
                                        |
                                  1. 驗證資料 (validate)
                                  2. ContentHelper::cleanHtml (防呆/安全性)
                                  3. Contact::create (存入 contact 表，狀態=0，產生序號)
                                  4. MailConfigHelper::applyFromDatabase (套用 SMTP 設定)
                                  5. Mail::send (發送通知信給客服 / 管理員)
                                        |
                                        v
                              (建立客服單完成)


[管理員登入] ---- 進入列表 ----> [Admin ContactController@index]
      |                                 |
      |                           1. 顯示分頁列表 (具記憶功能)
      |                                 |
      +---------- 點擊查看 ----> [Admin ContactController@edit]
                                        |
                                  1. 讀取 Contact 資料
                                  2. 更新狀態為 1 (已讀)
                                  3. 清理無效暫存圖 (SummernoteImageHelper)
                                  4. 顯示 Summernote 編輯器
                                        |
      +---------- 提交回覆 ----> [Admin ContactController@update]
                                        |
                                  1. [ DB Transaction Start ]
                                  |    2. ContentHelper::encodeSiteUrl (標籤化)
                                  |    3. ContactReply::create (存入 contact_reply)
                                  |    4. 更新狀態為 2 (已回覆)
                                  |    5. SummernoteImageHelper::commit (圖片轉正)
                                  |    6. MailConfigHelper::applyFromDatabase
                                  |    7. Mail::send (發送回覆信給客戶)
                                  |    8. 寫入操作日誌 (ActionLog)
                                  9. [ DB Transaction Commit ]
                                        |
                                        v
                                   [完成回覆]

</pre>

<pre>
[ 前台使用者 ]          [ Laravel 路由 ]          [ 邏輯處理層 ]          [ 外部/資料儲存 ]
      |                      |                      |                      |
      |-- 填寫表單內容 ------>|                      |                      |
      |-- (含 reCAPTCHA)     |-- POST /contact/store |                      |
      |                      |--------+             |                      |
      |                      |        |--[ 1. 驗證 ]|-- reCAPTCHA API 驗證 ->| (Google 伺服器)
      |                      |        |             |                      |
      |                      |        |--[ 2. 過濾 ]|-- ContentHelper 清理 ->|
      |                      |        |             |                      |
      |                      |        |--[ 3. 儲存 ]|-- 寫入 Contact 表 ---->| (Database)
      |                      |        |             |                      |
      |                      |        |--[ 4. 配置 ]|-- MailConfigHelper --+
      |                      |        |             |                      | |
      |                      |        |--[ 5. 發信 ]|-- 寄送通知信 -------->| (SMTP Server)
      |<-- 回傳 JSON 成功 ----|        |             |                      |
      |                      |        +-------------+                      |
      |                      |                                             |
[ 後台管理員 ]               |                                             |
      |                      |                                             |
      |-- 登入查看列表 ------>|-- GET /admin/contact |                      |
      |                      |--[ 狀態更新 ]-------->|-- 更新 status 為 1 --->| (Database)
      |                      |                      |                      |
      |-- 填寫回覆內容 ------>|-- PUT /contact/{id}  |                      |
      |                      |--------+             |                      |
      |                      |        |--[ 1. 事務 ]|-- DB::transaction    |
      |                      |        |--[ 2. 儲存 ]|-- 寫入 Reply 表 ----->| (Database)
      |                      |        |--[ 3. 更新 ]|-- status 改為 2 ------>| (Database)
      |                      |        |--[ 4. 發信 ]|-- 寄回覆信給客戶 ------>| (Email)
      |<-- Redirect 返回 -----|        +-------------+                      |
</pre>
---
---
## ===== 功能檔案清單與用途說明 =====

| 分類 | 檔案路徑 | 功能說明 | 主要用途 (解決什麼問題) |
| :--- | :--- | :--- | :--- |
| 系統核心 | bootstrap/app.php | 應用程式啟動配置 | 註冊 Middleware（如權限、語系）與全局異常處理（如 CSRF 過期跳轉）。 |
| 配置定義 | config/backend_permissions.php | 後台權限目錄 | 定義模組標籤與操作代碼，供系統自動生成標題與判斷權限。 |
| 基礎控制 | app/Http/Controllers/Admin/BaseAdminController.php | 後台基礎控制器 | 所有後台功能之母，處理分頁記憶、自動標題、AJAX 狀態切換與權限綁定。 |
| 資料驗證 | app/Http/Requests/Admin/BaseFormRequest.php | 基礎表單驗證器 | 集中定義全站統一的圖片與檔案上傳規則（如副檔名、大小限制）。 |
| 內容輔助 | app/Helpers/ContentHelper.php | 內容標籤化與過濾 | 解決開發/正式環境網址遷移問題（[[SITE_URL]]）並過濾危險 HTML 標籤。 |
| 影像處理 | app/Helpers/ImageHelper.php | 影像處理核心工具 | 負責圖片上傳後的裁切、縮放、補白背景與隨機檔名生成。 |
| 編輯器工具 | app/Helpers/SummernoteImageHelper.php | Summernote 圖片管理 | 比對新舊內容，自動刪除被移除的圖片，並清理未儲存的暫存圖檔。 |
| 模型擴充 | app/Traits/HasImageFields.php | 圖片欄位自動化處理 | 讓 Model 刪除資料時，自動連動刪除 ImageHelper 產生的實體檔案。 |
| 視覺元件 | resources/views/admin/page-message.blade.php | 統一操作結果頁面 | 顯示操作成功/失敗提示，並包含自動跳轉與自定義按鈕連結。 |
---
---
<pre>
[ 管理員操作 ]          [ 系統核心層 ]               [ 邏輯處理 / Helper 層 ]           [ 資料/實體儲存 ]
      |                      |                           |                           |
      |--- 1. 進入頁面 ----> [ bootstrap/app.php ]       |                           |
      |                      | (Middleware 檢查權限)      |                           |
      |                      |           |               |                           |
      |                      v           |               |                           |
      |                [ BaseAdminController ] <--- [ config/backend_permissions.php ]
      |                (自動產生頁面標題) |               (讀取模組中文名稱)             |
      |                      |           |               |                           |
      |--- 2. 上傳圖片 ----> [ ImageHelper ] ------------+------------> [ /storage/app/public ]
      |      (編輯器內)      (處理縮放/補色) |               |               (儲存實體圖檔)
      |                      |           |               |                           |
      |                      v           |               |                           |
      |--- 3. 提交表單 ----> [ BaseFormRequest ]         |                           |
      |                      (驗證圖片格式與大小)          |                           |
      |                              |                   |                           |
      |                              v                   |                           |
      |                      [ Controller@store ]        |                           |
      |                              |                   |                           |
      |                              |--[ ContentHelper::encodeSiteUrl ] ----------> [[SITE_URL]]
      |                              |   (將絕對網址轉為標籤存入 DB)                   (資料庫字串)
      |                              |                   |                           |
      |                              |--[ SummernoteImageHelper::commit ]            |
      |                              |   (確認存檔，移除暫存觀察名單)                     |
      |                              |                   |                           |
      |                              v                   |                           |
      |--- 4. 完成跳轉 <---- [ page-message.blade.php ]  |                           |
      |                      (顯示成功並倒數跳轉)          |                           |
      |                                                  |                           |
      |                                                  |                           |
[ 5. 刪除資料 ] ----------------------------------------> [ App\Models\XXX ]          |
      |                                                  (使用了 HasImageFields)      |
      |                                                          |                   |
      |                                                          v                   |
      |                                                  [ ImageHelper::delete ] --> [ 刪除實體檔案 ]
</pre>

