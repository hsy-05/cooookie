README_Content: |
  # 【COOOOKIE】專業餅乾型錄系統 - 全方位技術文件

  ## 1. 開發環境與核心技術棧
  * '開發環境': PHP 8.2 / Laravel 12.21.0 / MySQL 8.0
  * '核心套件':
      - '認證與安全性': Laravel Breeze (認證), Laravel Sanctum (API 基礎認證)
      - '後端效能': Laravel Task Scheduler & Queue (執行背景任務)
      - '測試驅動': PHPUnit (單元測試覆蓋主要核心功能)
  * '前端技術': HTML5 (語意化標籤), SCSS (RWD 全斷點設計), jQuery 3.7
  * 'UI/UX 工具': AdminLTE 3 (後台框架), Summernote (視覺化編輯器)

  ---

  ## 2. 後台管理系統 (CMS) - 模塊化設計與防呆邏輯

  ### '產品、消息與廣告管理 (多語系架構)'
  * **功能描述**: 針對不同內容（Product/News/Advert）建立獨立 Model 與 Desc 關聯表，支援橫向擴充語系。
  * **技術亮點**:
      - 抽離 'Loggable' Trait 自動記錄各模組操作紀錄。
      - 圖片上傳透過 'ImageHelper' 進行檔案格式、大小驗證，並依功能別（adv, product）自動分類儲存。
  * **操作邏輯**: 支援動態排序、顯示狀態調整。廣告模組具備區塊 (Category) 投遞邏輯。
  ![後台管理模組截圖](z_demo-images/admin/module_management.png)

  ### '客服管理與郵件通訊'
  * **功能流程**: 用戶提交聯絡表單 -> 'Contact' 紀錄生成 -> 管理員回覆 -> 觸發 'Mail/ContactNotification' 郵件通知。
  * **亮點**: 整合非同步通知，確保用戶體驗不受郵件發送時間影響。
  ![客服回覆系統介面](z_demo-images/admin/contact_system.png)

  ### '權限與系統日誌'
  * **角色分級**: '開發員' (結構維護)、'內部最高管理員' (全站管理)、'客戶管理員' (內容維運)。
  * **操作稽核**: 透過 'ActionLog' 詳細記錄管理員異動內容，包含操作人、時間與具體行為。
  ![系統紀錄與日誌畫面](z_demo-images/admin/logs_view.png)

  ---

  ## 3. 核心邏輯與架構分析

  ### '圖片管理與 Summernote 整合'
  * **特殊邏輯**: Summernote 上傳時由 'SummernoteImageHelper' 攔截，將圖片轉存為實體檔案而非儲存在資料庫（避免 DB 肥大）。
  * **路徑管理**: 統一儲存於 'public/storage/'，透過軟連結 (Symbolic Link) 實現前端安全存取。
  ![圖片處理邏輯圖](z_demo-images/logic/image_upload_logic.png)

  ### '後端維運機制'
  * **背景任務**: 使用 'Task Scheduler' 定期清理系統日誌或更新快取。
  * **任務隊列**: 'Queue' 處理耗時任務（如發送聯絡表單通知信），提升系統回應速度。

  ---

  ## 4. 前台呈現與風格 (User Experience)

  ### '呈現風格與互動'
  * **首頁佈局**: 整合 Banner 輪播、精選產品與最新消息，採分區明確的區塊化設計。
  * **產品列表**: 採用 '網格卡片式 (Card Grid)' 排版，支援 jQuery 動態分類篩選與分頁。
  * **產品詳情**: 圖片支援點擊互動放大，規格資訊清晰分欄。
  ![前台展示截圖](z_demo-images/frontend/home_showcase.png)

  ### 'SEO 與 RWD 規範'
  * **RWD 全斷點**: 嚴格適應 4K、1440px (Desktop)、1024px (Laptop)、768px (Tablet)、320px (Mobile)。
  * **SEO 動態化**: 標題、關鍵字與 Meta Description 由 'SystemSetting' 統一管理，確保搜尋引擎抓取最優化資訊。

  ---

  ## 5. 資料流與系統邏輯 (ASCII)

  ### '後台 CRUD 與圖片上傳資料流'
  ```
  [Request] -> [Request Class 驗證] -> [Controller] 
                                          |
                        -----------------------------------
                        |                |                |
                [ImageHelper]    [Desc Model]     [Main Model]
                        |                |                |
                [Storage/Files]   [多語系內容]       [主體紀錄]
  ```

  ### '背景任務流程 (Queue/Mail)'
  ```
  [前台表單提交] -> [Controller 存入資料庫] -> [Dispatch Queue Job]
                                                   |
                                         [背景執行 Mail 發送] -> [發送成功]
  ```

  ---

  ## 6. 安裝與測試說明
  * '環境準備': 
      1. 'composer install'
      2. 'cp .env.example .env' (配置 DB 資訊)
      3. 'php artisan migrate --seed' (包含基礎權限資料)
      4. 'npm install && npm run dev'
  * '單元測試': 執行 'php artisan test' 確保核心邏輯與權限驗證正確無誤。


  ---

  ## 7. 系統功能詳解與前後台介面導覽

  ### A. 後台管理系統 (CMS) 功能詳解

  #### '1. 廣告管理系統 (Advert Management)'
  * **介面預覽**: ![後台廣告管理畫面](z_demo-images/admin/advert_list.png)
  * **功能描述**: 維護全站輪播圖、廣告橫幅及其分類，並深度整合多語系關聯。
  * **特殊邏輯**:
      - 廣告本體與分類抽離（'AdvertCategory'），支援同一組廣告依區塊與排序設定投遞。
      - 多語系欄位獨立儲存於 'AdvertDesc' 表，確保前台切換語系時內容即時同步。
  * **防呆與參數**: 圖片限制最大 2MB、格式限 jpg, png，路徑統一由 'ImageHelper' 代碼端控制，非人為任意輸入。
  * **可操作角色**: '內部最高管理員'、具備廣告管理權限之'客戶管理員'。

  #### '2. 最新消息公告管理 (News Management)'
  * **介面預覽**: ![後台最新消息管理畫面](z_demo-images/admin/news_list.png)
  * **功能描述**: 管理網站活動、最新公告資訊，支援發布狀態與首頁置頂功能。
  * **特殊邏輯**: 分類、消息主體、多語系描述（'NewsDesc'）分離，降低主表複雜度。
  * **可調整參數**: 支援修改排序權重、決定是否同步顯示於前端首頁。

  #### '3. 產品型錄管理 (Product Catalog)'
  * **介面預覽**: ![後台產品管理畫面](z_demo-images/admin/product_list.png)
  * **功能描述**: 維護【COOOOKIE】全站手工餅乾品項、分類與詳細說明。
  * **技術亮點**: 編輯器高度整合 'SummernoteImageHelper'，將富文本中的圖片異步轉存實體硬碟，根絕 Base64 造成資料庫效能低落的問題。
  * **可操作角色**: '客戶管理員'（僅限日常上下架維護）。

  #### '4. 客服聯絡管理 (Contact Customer Service)'
  * **介面預覽**: ![後台客服回覆畫面](z_demo-images/admin/contact_manage.png)
  * **功能描述**: 集中審查前台留言，並可直接進行線上回覆。
  * **技術亮點**: 回覆確認送出後，系統會自動加入佇列（'Queue'），透過 'Mail/ContactNotification' 發送非同步電子郵件給用戶，保障操作介面流暢、不卡頓。

  #### '5. 語系核心設定 (Language Settings)'
  * **介面預覽**: ![後台語系設定畫面](z_demo-images/admin/language_settings.png)
  * **功能描述**: 控管全站前台所支援的語言種類與預設語系切換。
  * **操作特性**: 新增或移除語系將即時反映於全站多語系欄位表（'Desc Tables'）的存取基礎。

  #### '6. 管理員安全稽核紀錄 (Action Logs)'
  * **介面預覽**: ![後台操作紀錄畫面](z_demo-images/admin/action_logs.png)
  * **功能描述**: 全自動跟蹤並記錄後台管理員的一舉一動，防範內部越權或操作失誤。
  * **技術亮點**: 套用 'Loggable' Trait，無需在每個 Controller 寫重複程式碼，系統會在 CRUD 發生時自動擷取操作人、時間與變更內容。

  #### '7. 角色權限矩陣管理 (Role & RBAC)'
  * **介面預覽**: ![後台角色管理畫面](z_demo-images/admin/role_management.png)
  * **功能描述**: 定義彈性的角色權限，將權限精細劃分至特定選單。
  * **核心邏輯**: 變更權限後，系統透過 'CheckBackendPermission' Middleware 進行即時路由攔截，阻斷非法越權存取。

  #### '8. 後台帳號控管 (Admin Users)'
  * **介面預覽**: ![後台管理員設定畫面](z_demo-images/admin/admin_users.png)
  * **功能描述**: 後台帳號的生命週期管理（新增、停用、指派角色）。
  * **特殊邏輯**: 為防止系統癱瘓，限制非開發員角色不得更動或刪除初始'開發員'帳號。

  #### '9. 系統級維運日誌 (System Logs)'
  * **介面預覽**: ![後台系統日誌畫面](z_demo-images/admin/system_logs.png)
  * **功能描述**: 讀取底層框架與伺服器發生的異常、警告與錯誤。
  * **維運特點**: 提供關鍵字、層級（Error/Warning）篩選，供工程師快速定位線上問題。

  #### '10. 通用通用圖片上傳組件 (Universal Image Uploader)'
  * **介面預覽**: ![圖片上傳組件範例](z_demo-images/admin/image_uploader.png)
  * **功能描述**: 橫跨廣告、產品、消息的中央圖片處理引擎。
  * **技術亮點**: 由 'ImageHelper' 獨家控管，自動執行檔名唯一化雜湊（Hash）、強制壓縮並分流儲存於 'public/storage/adv/' 或 'public/storage/product/'，避免目錄混亂。

  ---

  ### B. 前台使用者介面 (Frontend UI/UX) 與風格呈獻

  #### '1. 首頁 (Home Page)'
  * **頁面預覽**: ![前台首頁畫面](z_demo-images/frontend/home.png)
  * **視覺風格**: 採用溫暖的手作餅乾品牌主色，版面區塊（Banner 輪播、精選商品、最新消息）動態交織，傳遞高品質形象。
  * **前端互動**: jQuery 動態驅動 Banner 輪播切換，點擊卡片平滑導向詳情頁。

  #### '2. 商品型錄列表 (Product List)'
  * **頁面預覽**: ![前台商品列表畫面](z_demo-images/frontend/product_list.png)
  * **排版結構**: 採標準的**網格卡片式 (Card Grid)** 排版，兼顧視覺飽滿度與 RWD 適應性。
  * **前端互動**: 無重新整理的 jQuery 分類無縫篩選、即時排序與響應式分頁組件。
  * **結構化標籤**: 採用 '<article>' 與合理 Heading 階層，優化 Google 機器人爬取效率。

  #### '3. 商品詳情頁 (Product Detail)'
  * **頁面預覽**: ![前台商品詳情畫面](z_demo-images/frontend/product_detail.png)
  * **呈現方式**: 採左圖右文的大氣排版。左側產品圖支援 jQuery 點擊互動放大與多圖切換，右側重點商品規格加粗顯示。

  #### '4. 最新消息公告 (News Center)'
  * **頁面預覽**: ![前台消息列表畫面](z_demo-images/frontend/news_list.png)
  * **呈現方式**: 清晰的清單/卡片混合流，標題按時間權重清晰排列。支援按消息類別進行前端快速過濾。

  #### '5. 聯絡我們表單 (Contact Us)'
  * **頁面預覽**: ![前台聯絡我們畫面](z_demo-images/frontend/contact_form.png)
  * **互動亮點**: 具備即時前端 jQuery 表單欄位不為空與 Email 格式防呆驗證。按下送出按鈕後，按鈕自動進入 'disabled'（禁用狀態）並顯示「處理中...」，防止使用者重複點擊造成後端重複寫入資料。
