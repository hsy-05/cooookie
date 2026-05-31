README_Content: |

# 【COOOOKIE】餅乾型錄網站 - 相關說明

## ● 開發環境與核心技術棧

- '開發環境': PHP 8.2 / Laravel 12.21.0 / MySQL 8.0
- '核心套件':
    - '認證與安全性': Laravel Breeze (認證), Laravel Sanctum (API 基礎認證)
    - '後端效能': Laravel Task Scheduler & Queue (執行背景任務)
    - '測試驅動': PHPUnit (單元測試覆蓋主要核心功能)
- '系統監控與日誌':
    - 'Log 管理工具': opcodesio/log-viewer (^3.21)（後台錯誤與系統日誌檢視）
- '前端技術': HTML5 , CSS , jQuery 3.7, Javascript
- 'UI/UX 工具': AdminLTE 3 (後台框架), Summernote (內容編輯器)

---

<!--
  ## ● 安裝與測試說明
  * '環境準備':
      1. 'composer install'
      2. 'cp .env.example .env' (配置 DB 資訊)
      3. 'php artisan migrate --seed' (包含基礎權限資料)
      4. 'npm install && npm run dev'
  * '單元測試': 執行 'php artisan test' 確保核心邏輯與權限驗證正確無誤。
 -->

---

## ● 系統功能詳解與前後台介面導覽

### A. 後台管理系統 (CMS) 功能詳解

#### '1. 廣告管理系統 (Advert Management)'

- **介面預覽**: ![後台廣告管理畫面](z_demo-images/admin/advert_list.png)
- **功能描述**: 維護全站輪播圖、廣告橫幅及其分類，並深度整合多語系關聯。
- **特殊邏輯**:
    - 廣告本體與分類抽離（'AdvertCategory'），支援同一組廣告依區塊與排序設定投遞。
    - 多語系欄位獨立儲存於 'AdvertDesc' 表，確保前台切換語系時內容即時同步。
- **防呆與參數**: 圖片限制最大 2MB、格式限 jpg, png，路徑統一由 'ImageHelper' 代碼端控制，非人為任意輸入。
- **可操作角色**: '內部最高管理員'、具備廣告管理權限之'客戶管理員'。

#### '2. 最新消息公告管理 (News Management)'

- **介面預覽**: ![後台最新消息列表截圖](z_demo-images/admin/news_list.png)

<div style="display:flex; justify-content:center; flex-wrap:wrap; gap:10px;">
  <img src="z_demo-images/admin/news_form1.png" width="450">
  <img src="z_demo-images/admin/news_form2.png" width="450">
</div>

- **功能描述**: 管理網站活動、最新公告資訊，支援發布狀態與首頁置頂功能。
- **特殊邏輯**: 分類、消息主體、多語系描述（'NewsDesc'）分離，降低主表複雜度。
- **可調整參數**: 支援修改排序權重、決定是否同步顯示於前端首頁。

#### '3. 產品型錄管理 (Product Catalog)'

- **介面預覽**: ![後台產品管理畫面](z_demo-images/admin/product_form1.png)
- **功能描述**: 維護【COOOOKIE】全站手工餅乾品項、分類與詳細說明。
- **技術亮點**: 編輯器高度整合 'SummernoteImageHelper'，將富文本中的圖片異步轉存實體硬碟，根絕 Base64 造成資料庫效能低落的問題。
- **可操作角色**: '客戶管理員'（僅限日常上下架維護）。

#### '4. 客服聯絡管理 (Contact Customer Service)'

- **介面預覽**: ![客服回覆系統介面](z_demo-images/admin/contact_system.png)
- **功能描述**: 集中審查前台留言，並可直接進行線上回覆。
- **技術亮點**: 回覆確認送出後，系統會自動加入佇列（'Queue'），透過 'Mail/ContactNotification' 發送非同步電子郵件給用戶，保障操作介面流暢、不卡頓。

#### '5. 語系核心設定 (Language Settings)'

- **介面預覽**: ![後台語系設定畫面](z_demo-images/admin/language_settings.png)
- **功能描述**: 控管全站前台所支援的語言種類與預設語系切換。
- **操作特性**: 新增或移除語系將即時反映於全站多語系欄位表（'Desc Tables'）的存取基礎。

#### '6. 管理員安全稽核紀錄 (Action Logs)'

- **介面預覽**: ![後台操作紀錄畫面](z_demo-images/admin/action_logs.png)
- **功能描述**: 全自動跟蹤並記錄後台管理員的一舉一動，防範內部越權或操作失誤。
- **技術亮點**: 套用 'Loggable' Trait，無需在每個 Controller 寫重複程式碼，系統會在 CRUD 發生時自動擷取操作人、時間與變更內容。

#### '7. 角色權限矩陣管理 (Role & RBAC)'

- **介面預覽**: ![後台角色管理畫面](z_demo-images/admin/role_management.png)
- **功能描述**: 定義彈性的角色權限，將權限精細劃分至特定選單。
- **核心邏輯**: 變更權限後，系統透過 'CheckBackendPermission' Middleware 進行即時路由攔截，阻斷非法越權存取。

#### '8. 後台帳號控管 (Admin Users)'

- **介面預覽**: ![後台管理員設定畫面](z_demo-images/admin/admin_users.png)

    <div style="display:flex; justify-content:center; flex-wrap:wrap; gap:10px;">
    <img src="z_demo-images/admin/admin_users_form1.png" width="300">
    <img src="z_demo-images/admin/admin_users_form2.png" width="300">
    </div>

- **功能描述**: 後台帳號的生命週期管理（新增、停用、指派角色）。
- **特殊邏輯**: 為防止系統癱瘓，限制非開發員角色不得更動或刪除初始'開發員'帳號。

  <!-- #### '9. 系統錯誤日誌 (System Logs) - 僅開發者可見'
  * **介面預覽**: ![後台系統日誌畫面](z_demo-images/admin/system_logs.png)
  * **功能描述**: 使用套件「Log Viewer」讀取底層框架與伺服器發生的異常、警告與錯誤。 -->

#### '9. 通用通用圖片上傳組件 (Universal Image Uploader)'

- **介面預覽**: ![圖片上傳組件範例](z_demo-images/admin/image_uploader.png)
- **功能描述**: 橫跨廣告、產品、消息的中央圖片處理引擎。
- **技術亮點**: 由 'ImageHelper' 獨家控管，自動執行檔名唯一化雜湊（Hash）、強制壓縮並分流儲存於 'public/storage/adv/' 或 'public/storage/product/'，避免目錄混亂。

---

### B. 前台使用者介面 (Frontend UI/UX) 與風格呈獻

#### '※ 首頁 (Home Page)'

- **頁面預覽**: <p align='center'><img src='z_demo-images/frontend/home.png' width='600'></p>
- **視覺風格**: 採用溫暖的手作餅乾品牌主色，版面區塊（Banner 輪播、精選商品、最新消息）動態交織，傳遞高品質形象。
- **前端互動**: jQuery 動態驅動 Banner 輪播切換，點擊卡片平滑導向詳情頁。

#### '※ 商品型錄列表 (Product List)'

- **頁面預覽**: <p align='center'><img src='z_demo-images/frontend/product_list.png' width='600'></p>
- **排版結構**: 採標準的**網格卡片式 (Card Grid)** 排版，兼顧視覺飽滿度與 RWD 適應性。
- **前端互動**: 無重新整理的 jQuery 分類無縫篩選、即時排序與響應式分頁組件。
- **結構化標籤**: 採用 '<article>' 與合理 Heading 階層，優化 Google 機器人爬取效率。

#### '※ 最新消息公告 (News Center)'

- **頁面預覽**: <p align='center'><img src='z_demo-images/frontend/news_list.png' width='600'></p>
- **呈現方式**: 清晰的清單/卡片混合流，標題按時間權重清晰排列。支援按消息類別進行前端快速過濾。

#### '※ 聯絡我們表單 (Contact Us)'

- **頁面預覽**: <p align='center'><img src='z_demo-images/frontend/contact_form.png' width='600'></p>
- **互動亮點**: 具備即時前端 jQuery 表單欄位不為空與 Email 格式防呆驗證。
- **UX 設計**: 按下送出按鈕後，按鈕自動進入 'disabled' 並顯示「處理中...」，防止使用者重複點擊造成後端重複寫入資料。
