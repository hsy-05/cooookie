# 專案架構說明（Backend）

本文件說明本專案 app 資料夾中各模組的設計目的與使用原則，
用於團隊開發、交接與維護。

---

# 架構設計核心原則

1. Controller 只負責請求與回應
2. 業務邏輯集中於 Service Layer
3. 共用行為以 Event / Listener 與 Trait 實現
4. 避免萬用 Helper，確保責任單一

---

## app 資料夾結構說明

### app/Helpers
（僅作為過渡使用，未來將逐步淘汰）

- ContentHelper.php  
- ImageHelper.php  
- SummernoteImageHelper.php  

說明：
目前專案仍保留 Helper 作為共用工具，
但凡涉及「業務意義」的邏輯，應優先使用 Service。

---

### app/Services（核心業務邏輯）

- Content  
  - UrlFormatterService.php  
- Image  
  - ImageProcessService.php  
  - SummernoteImageCleanupService.php  

說明：
Service Layer 用於承載業務邏輯，
可被 Controller、Listener、Trait 呼叫。

---

### app/Listeners

- LogSuccessfulLogin.php  

說明：
負責處理系統事件發生後的延伸行為，
避免 Controller 過度肥大。

---

### app/Traits

- Loggable.php  

說明：
用於掛載 Model 共用行為，
實際業務邏輯應交由 Service 處理。

---

### app/Providers

- AppServiceProvider.php  
- RouteServiceProvider.php  

說明：
僅用於系統初始化與全域設定，
禁止放置業務邏輯。

---

## 架構維護原則

- 新功能開發時，優先建立 Service
- Helper 不得新增業務邏輯
- Trait 僅處理事件掛載，不直接操作 DB
- 所有圖片與內容處理需集中管理

---

## 新進人員注意事項

- 請勿在 Controller 中撰寫圖片處理或內容轉換邏輯
- 若不確定程式碼應放位置，請先詢問或查看既有 Service




以下是三個資料夾，裡面有的檔案中分別裡面有哪些function的功能，根據裡面的檔案去分析為何要分這麼多資料夾跟檔案？分別的用途是甚麼？
並用專業網頁設計公司角度判斷是否需要這樣分資料夾，依照專業的方式其他有更好維護的寫法架構。
用專業網頁設計公司角度去思考新手需要了解的所有想觀的問題和回答，請全部列出來。

C:\web\wwwroot82\cooookie\app\Helpers
    -> ContentHelper.php：儲存時將完整 URL 換成 [[SITE_URL]] 標記、顯示時：將 [[SITE_URL]] 標記還原成完整 URL、顯示提示訊息頁面

    -> ImageHelper.php：處理圖片裁切 / 縮圖 / 補背景等功能、生成一個唯一的檔名、將處理後的圖片編碼並儲存到指定路徑、刪除圖片檔案（支援單一或批量）

    -> SummernoteImageHelper.php：清理 Summernote 內容中被刪除的圖片檔案（靜態方法）。比較舊內容和新內容中的圖片 URL，刪除舊內容中有但新內容中沒有的圖片檔案。、從 HTML 內容中提取所有 <img src="..."> 的 URL（靜態方法）。只提取以 /storage/{$imageSubDir}/ 開頭的圖片 URL。

C:\web\wwwroot82\cooookie\app\Listeners
    ->LogSuccessfulLogin.php：當登入發生時，Laravel 會把 Login $event 傳進來

C:\web\wwwroot82\cooookie\app\Providers
    ->AppServiceProvider.php：Register any application services.、Bootstrap any application services.
    ->RouteServiceProvider.php：使用者登入後的預設導向位置


---
# 調整路徑紀錄
---

## 當管理員「已登入」卻嘗試進入 login 或 register 頁面時，強制轉向到指定路徑
C:\web\wwwroot82\cooookie\bootstrap\app.php

    $middleware->redirectUsersTo('/admin');

- - -
