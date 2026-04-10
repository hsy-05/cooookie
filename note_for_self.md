# 🍪 【COOOOKIE】專案開發維護筆記 (完整存檔版)

本文件紀錄專案核心架構與功能邏輯，方便快速上手與維護。

---

# 🚀 一、 架構設計核心原則

1. **Controller 只負責請求與回應**：不撰寫複雜業務邏輯，僅調度 Trait 或 Helper。
2. **邏輯解耦 (Helper / Trait)**：本專案未實作 app/Services，邏輯集中於靜態工具類與模型擴充技能。
3. **共用行為自動化**：透過 Trait 監聽 Model 事件（如 deleted）實現自動化清理。
4. **搬站防破圖機制**：內容 URL 標籤化，儲存為 [[SITE_URL]]。

---

## 📂 二、 檔案功能清單與用途說明

### 1. 資料夾分類分類
| 分類 | 說明 |
| :--- | :--- |
| **app/Http/Middleware** | 進入頁面前的攔截器，處理權限檢查與 CSRF 排除。 |
| **app/Helpers** | 存放靜態工具，如文字過濾 (Content)、圖片處理 (Image)、編輯器清理 (SummernoteImage)。 |
| **app/Traits** | 賦予 Model 自動化能力，如操作紀錄 (Loggable)、圖片同步刪除 (HasImageFields)。 |
| **app/Http/Requests/Admin** | 統一驗證規則，如圖片大小與副檔名限制。 |
| **public/js/admin** | 後台通用 JS，如 common.js 處理 AJAX 刪除請求。 |

### 2. 關鍵檔案清單
| 分類 | 檔案路徑 | 功能說明 |
| :--- | :--- | :--- |
| 系統核心 | bootstrap/app.php | 註冊 Middleware 與全局異常處理。 |
| 基礎控制 | App/Http/Controllers/Admin/BaseAdminController.php | 處理分頁記憶、自動標題、及 $this->view 變數注入。 |
| 內容輔助 | App/Helpers/ContentHelper.php | 處理 [[SITE_URL]] 標籤化與危險 HTML 過濾。 |
| 影像處理 | App/Helpers/ImageHelper.php | 處理圖片上傳裁切、補白背景與實體檔案刪除。 |
| 編輯器工具 | App/Helpers/SummernoteImageHelper.php | 比對內容差異，刪除沒被使用的冗餘圖檔。 |
| 模型擴充 | App/Traits/HasImageFields.php | 監聽 Model deleted 事件，自動連動刪除實體檔案。 |
| 模型擴充 | App/Traits/Loggable.php | 提供 writeLog 方法，支援自動/手動紀錄操作日誌。 |
| 視覺元件 | resources/views/admin/page-message.blade.php | 統一顯示操作成功/失敗提示及自動跳轉。 |

---

## 📝 三、 核心邏輯流程圖

### 1. HasImageFields.php 觸發流程圖 (AJAX 刪除欄位圖片)

<pre>
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
</pre>

### 2. 檔案自動化清理流程 (整筆刪除)

<pre>
[ Controller 執行 $news->delete() ]
       |
       v
[ Model 觸發 static::deleted 事件 ]
       |
       v
[ HasImageFields Trait ]
       |
       |-- 1. getImageFields() 讀取屬性定義 (如 image_url)
       |-- 2. 判斷欄位是否有值
       |-- 3. ImageHelper::deleteImage(實體路徑)
       v
[ 實體檔案從硬碟移除 ]
</pre>

### 3. 多語系設定邏輯 (後台編輯 vs. 系統顯示)

<pre>
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
Model $news->title -> 自動去抓 lang_id = 1 的描述 (Accessor 邏輯)
      |
      v
畫面顯示：「中文標題」
</pre>

### 4. 權限與操作紀錄邏輯流程圖

<pre>
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
</pre>

### 5. 聯絡我們 (Contact) 交互流程

<pre>
[前台使用者] ---- 填寫表單 ----> [Frontend ContactController@store]
                                        |
                                  1. 驗證資料 (validate / reCAPTCHA)
                                  2. ContentHelper::cleanHtml (安全性)
                                  3. Contact::create (狀態=0，產生序號)
                                  4. MailConfigHelper::applyFromDatabase (套用 SMTP)
                                  5. Mail::send (發送通知信給客服)
                                        |
                                        v
                                  (建立客服單完成)

[管理員回覆] ---- 提交內容 ----> [Admin ContactController@update]
                                        |
                                  1. [ DB Transaction Start ]
                                  2. ContentHelper::encodeSiteUrl (標籤化)
                                  3. ContactReply::create (存入回覆表)
                                  4. 更新主表狀態為 2 (已回覆)
                                  5. SummernoteImageHelper::commit (圖片轉正)
                                  6. Mail::send (發送回覆信給客戶)
                                  7. [ DB Transaction Commit ]
</pre>

---

## 🔑 四、 核心功能開發守則

### 1. 渲染畫面規範 ($this->view)
- ❌ **禁止使用** 全域 view()：會導致遺失父類別定義好的全域變數 $sys。
- ✅ **必須使用** $this->view()：
  - 自動掛載 PageTitle。
  - 自動處理後台 URL 記憶。
  - 確保語系與權限變數正確傳遞。

### 2. Model 模型擴充 (News.php)
- **多語系存取**：使用 $news->title 即可取得當前語系標題，底層由 getTitleAttribute Accessor 處理。
- **操作紀錄標題**：透過 getLogTitleAttribute 定義 Log 顯示的標題。
- **自動化開關**：$enableAutoLog = false 代表關閉自動監聽，改由 Controller 根據邏輯精確手動觸發 writeLog()。

---

## 💡 五、 新手 FAQ (面試必備)

**Q：為什麼要把圖片刪除放在 Trait 的 deleted 事件？**
答：因為這能保證「資料庫刪除成功後才刪實體檔案」，且不論是透過 Controller 還是 Seeder 刪除資料，只要執行了 Model 的 delete()，清理邏輯都會觸發，不會產生孤兒檔案。

**Q：為什麼要存 [[SITE_URL]] 而不存絕對路徑？**
答：這是專業開發的標準作法。避免在環境遷移（從開發機到測試機或正式站）時，因為網域或 SSL 協議變更導致圖片全數破圖。

**Q：如何防止 Summernote 產生大量垃圾圖檔？**
答：
1. **儲存時**：syncEditorImages 會比對 HTML 內容，把「曾經存在但後來被使用者刪掉」的圖片路徑找出來並刪除實體檔案。
2. **結案暫存**：commitTempImages 會把 Session 中的暫存標記清除。
3. **過期清理**：cleanAbandonedImages 掃除那些「傳了圖卻沒存檔就跑掉」的過期圖片。
