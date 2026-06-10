user-ask: @workspace

請列出這個 Laravel 專案的完整資料夾與檔案結構（包含/app中所有自訂資料夾，例如： Models、Services、Helpers、Providers 等）。

要求：
1. 必須完整列出，不可省略任何資料夾
2. 每個資料夾請說明用途（簡短一句）
3. 如果用途不確定請標註「推測」
4. 請用樹狀結構（tree format）輸出

GitHub Copilot: 我會幫您完整列出這個 Laravel 專案的資料夾與檔案結構。首先，讓我深入探索工作區的所有資料夾。 現在讓我繼續探索更多資料夾： 繼續探索控制器子資料夾： 現在探索請求類別和其他資料夾： 探索 View Components： 繼續探索更多資料夾： 探索更多詳細結構： 完成最後的資料夾探索： 最後探索一些剩餘的資料夾： 完美！現在我有了完整的資料夾結構。讓我為您組織成樹狀格式的完整文檔：

---

# Laravel 專案完整資料夾與檔案結構

## 根目錄結構

```
cooookie/
│
├── 📄 根目錄配置檔案
│   ├── .editorconfig                    # 編輯器配置
│   ├── .env                             # 環境變數
│   ├── .env.example                     # 環境變數範本
│   ├── .gitattributes                   # Git 屬性配置
│   ├── .gitignore                       # Git 忽略清單
│   ├── .styleci.yml                     # StyleCI 配置
│   ├── artisan                          # Laravel CLI 工具
│   ├── composer.json                    # Composer 依賴配置
│   ├── composer.lock                    # Composer 依賴鎖定
│   ├── package.json                     # Node.js 依賴配置
│   ├── package-lock.json                # Node.js 依賴鎖定
│   ├── phpunit.xml                      # PHPUnit 測試配置
│   ├── README.md                        # 專案說明文檔
│   ├── vite.config.js                   # Vite 打包工具配置
│   ├── CHANGELOG.md                     # 更新日誌
│   ├── 檔案說明.txt                      # 檔案說明
│   └── 備份 SQL 檔案 (4 個)
│
├── 🗂️ app/                              # Laravel 應用核心程式碼
│   ├── 📂 Http/                         # HTTP 相關邏輯
│   │   ├── Controllers/                 # 控制器
│   │   │   ├── Controller.php           # 基礎控制器
│   │   │   ├── HomeController.php       # 首頁控制器
│   │   │   ├── ProfileController.php    # 用戶資料控制器
│   │   │   ├── UploadController.php     # 上傳控制器
│   │   │   │
│   │   │   ├── 📂 Admin/                # 後台管理控制器
│   │   │   │   ├── BaseAdminController.php         # 基礎後台控制器
│   │   │   │   ├── DashboardController.php        # 儀表板
│   │   │   │   ├── ActionLogController.php        # 操作日誌
│   │   │   │   ├── AdminUserController.php        # 管理員帳號管理
│   │   │   │   ├── AdminRoleController.php        # 角色權限管理
│   │   │   │   ├── LanguageController.php         # 多語言管理
│   │   │   │   ├── NewsCategoryController.php     # 新聞分類管理
│   │   │   │   ├── NewsController.php             # 新聞管理
│   │   │   │   ├── AdvertCategoryController.php   # 廣告分類管理
│   │   │   │   ├── AdvertController.php           # 廣告管理
│   │   │   │   ├── ProductCategoryController.php  # 產品分類管理
│   │   │   │   ├── ProductController.php          # 產品管理
│   │   │   │   ├── ContactController.php          # 聯絡表單管理
│   │   │   │   ├── SystemSettingController.php    # 系統設定管理
│   │   │   │   ├── SystemLogController.php        # 系統日誌
│   │   │   │   └── ClearCacheController.php       # 清除快取
│   │   │   │
│   │   │   ├── 📂 AdminAuth/            # 後台認證控制器
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   │
│   │   │   ├── 📂 Auth/                 # 前台認證控制器
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   │
│   │   │   └── 📂 Frontend/             # 前台功能控制器
│   │   │       ├── HomeController.php
│   │   │       ├── AboutController.php
│   │   │       ├── ContactController.php
│   │   │       ├── NewsController.php
│   │   │       └── ProductController.php
│   │   │
│   │   ├── 📂 Middleware/               # HTTP 中介層（請求攔截器）
│   │   │   ├── AdminThemeMiddleware.php          # 後台主題設置
│   │   │   ├── CheckBackendPermission.php       # 後台權限檢查
│   │   │   ├── EncryptCookies.php               # Cookie 加密
│   │   │   ├── SetLocale.php                    # 設置語言
│   │   │   └── VerifyCsrfToken.php              # CSRF 令牌驗證
│   │   │
│   │   └── 📂 Requests/                 # 表單驗證請求類
│   │       ├── ProfileUpdateRequest.php # 共用：用戶資料驗證
│   │       │
│   │       ├── 📂 Admin/                # 後台表單驗證
│   │       │   ├── BaseFormRequest.php          # 基礎驗證類
│   │       │   ├── ContactRequest.php
│   │       │   ├── NewsCategoryRequest.php
│   │       │   ├── NewsRequest.php
│   │       │   ├── ProductCategoryRequest.php
│   │       │   ├── ProductRequest.php
│   │       │   └── UpdateSystemSettingRequest.php
│   │       │
│   │       ├── 📂 Auth/                 # 認證表單驗證
│   │       │   └── LoginRequest.php
│   │       │
│   │       └── 📂 Frontend/             # 前台表單驗證
│   │           └── ContactRequest.php
│   │
│   ├── 📂 Models/                       # 資料模型（ORM 對應資料表）
│   │   ├── User.php                     # 用戶模型
│   │   ├── Admin.php                    # 管理員模型
│   │   ├── AdminRole.php                # 角色模型
│   │   ├── ActionLog.php                # 操作日誌模型
│   │   ├── Language.php                 # 語言模型
│   │   ├── News.php                     # 新聞模型
│   │   ├── NewsCategory.php             # 新聞分類模型
│   │   ├── NewsDesc.php                 # 新聞描述（多語言）模型
│   │   ├── NewsCategoryDesc.php         # 新聞分類描述（多語言）模型
│   │   ├── Advert.php                   # 廣告模型
│   │   ├── AdvertCategory.php           # 廣告分類模型
│   │   ├── AdvertDesc.php               # 廣告描述（多語言）模型
│   │   ├── AdvertCategoryDesc.php       # 廣告分類描述（多語言）模型
│   │   ├── Product.php                  # 產品模型
│   │   ├── ProductCategory.php          # 產品分類模型
│   │   ├── ProductDesc.php              # 產品描述（多語言）模型
│   │   ├── ProductCategoryDesc.php      # 產品分類描述（多語言）模型
│   │   ├── Contact.php                  # 聯絡表單模型
│   │   ├── ContactReply.php             # 聯絡回覆模型
│   │   └── SystemSetting.php            # 系統設定模型
│   │
│   ├── 📂 Helpers/                      # 輔助函數和工具類
│   │   ├── ContentHelper.php            # 內容處理輔助
│   │   ├── ImageHelper.php              # 圖片處理輔助
│   │   ├── TagHelper.php                # 標籤處理輔助
│   │   ├── MailConfigHelper.php         # 郵件配置輔助
│   │   └── SummernoteImageHelper.php    # 富文本編輯器圖片輔助
│   │
│   ├── 📂 Providers/                    # 服務提供者（依賴注入和啟動）
│   │   ├── AppServiceProvider.php       # 應用服務提供者
│   │   └── RouteServiceProvider.php     # 路由服務提供者
│   │
│   ├── 📂 Listeners/                    # 事件監聽器
│   │   └── LogSuccessfulLogin.php       # 記錄成功登入事件
│   │
│   ├── 📂 Mail/                         # 郵件類
│   │   └── ContactNotification.php      # 聯絡通知郵件
│   │
│   ├── 📂 Traits/                       # 特性（可重用的程式碼片段）
│   │   ├── HasImageFields.php           # 圖片欄位處理特性
│   │   ├── HasTagFields.php             # 標籤欄位處理特性
│   │   └── Loggable.php                 # 操作日誌記錄特性
│   │
│   └── 📂 View/                         # Vue 元件和佈局
│       ├── AppLayout.php                # 應用主佈局元件
│       ├── GuestLayout.php              # 訪客佈局元件（登入/註冊）
│       └── 📂 Components/               # Vue 元件
│           ├── 📂 Adminlte/             # AdminLTE 框架元件
│           │   ├── 📂 Form/             # 表單元件
│           │   ├── 📂 Layout/           # 佈局元件
│           │   ├── 📂 Tool/             # 工具元件
│           │   └── 📂 Widget/           # 小工具元件
│           └── (推測) 前台元件資料夾
│
├── 🗂️ config/                           # 配置檔案
│   ├── app.php                          # 應用配置
│   ├── auth.php                         # 認證配置
│   ├── cache.php                        # 快取配置
│   ├── database.php                     # 資料庫配置
│   ├── filesystems.php                  # 檔案系統配置
│   ├── logging.php                      # 日誌配置
│   ├── mail.php                         # 郵件配置
│   ├── queue.php                        # 隊列配置
│   ├── session.php                      # Session 配置
│   ├── services.php                     # 第三方服務配置
│   ├── adminlte.php                     # AdminLTE 後台框架配置
│   ├── backend_permissions.php          # 後台權限設定
│   ├── constants.php                    # 應用常數定義
│   ├── log-viewer.php                   # 日誌查看器配置
│   ├── purifier.php                     # HTML 淨化器配置
│   └── site_settings.php                # 網站設定
│
├── 🗂️ bootstrap/                        # 應用啟動
│   ├── app.php                          # Laravel 應用實例
│   ├── providers.php                    # 服務提供者列表
│   └── 📂 cache/                        # 啟動快取
│
├── 🗂️ routes/                           # 路由定義
│   ├── web.php                          # 網頁路由
│   ├── auth.php                         # 認證路由
│   └── console.php                      # 控制台命令路由
│
├── 🗂️ resources/                        # 前端資源（視圖、樣式、腳本）
│   ├── 📂 views/                        # Blade 模板檔案
│   │   ├── blade-files.txt              # Blade 檔案清單
│   │   ├── list_blade_files.bat         # 批次檔案生成清單
│   │   │
│   │   ├── 📂 admin/                    # 後台管理視圖
│   │   │   ├── dashboard.blade.php      # 儀表板
│   │   │   │
│   │   │   ├── 📂 admins/               # 管理員管理視圖
│   │   │   │   ├── form.blade.php
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── item_row.blade.php
│   │   │   │   └── permission_list.blade.php
│   │   │   │
│   │   │   ├── 📂 languages/            # 語言管理視圖
│   │   │   ├── 📂 roles/                # 角色管理視圖
│   │   │   │
│   │   │   ├── 📂 news_category/        # 新聞分類管理視圖
│   │   │   ├── 📂 news/                 # 新聞管理視圖
│   │   │   │
│   │   │   ├── 📂 advert_category/      # 廣告分類管理視圖
│   │   │   ├── 📂 advert/               # 廣告管理視圖
│   │   │   │
│   │   │   ├── 📂 product_category/     # 產品分類管理視圖
│   │   │   ├── 📂 product/              # 產品管理視圖
│   │   │   │
│   │   │   ├── 📂 contact/              # 聯絡表單管理視圖
│   │   │   ├── 📂 system_settings/      # 系統設定視圖
│   │   │   ├── 📂 system/               # 系統管理視圖
│   │   │   │   └── logs.blade.php       # 系統日誌
│   │   │   ├── 📂 logs/                 # 操作日誌視圖
│   │   │   └── 📂 auth/                 # (推測) 後台認證視圖
│   │   │
│   │   ├── 📂 frontend/                 # 前台視圖
│   │   │   ├── about.blade.php          # 關於我們
│   │   │   ├── contact.blade.php        # 聯絡表單
│   │   │   │
│   │   │   ├── 📂 layouts/              # 前台佈局
│   │   │   ├── 📂 news/                 # 新聞列表和詳情視圖
│   │   │   ├── 📂 news - demo/          # (推測) 新聞示例
│   │   │   │
│   │   │   └── 📂 product/              # 產品列表和詳情視圖
│   │   │       ├── index.blade.php      # 列表
│   │   │       └── show.blade.php       # 詳情
│   │   │
│   │   ├── 📂 auth/                     # 認證相關視圖（登入/註冊）
│   │   ├── 📂 profile/                  # 用戶資料視圖
│   │   ├── 📂 emails/                   # 郵件樣板
│   │   ├── 📂 components/               # 可重用 Blade 元件
│   │   │   ├── 📂 admin/                # 後台元件
│   │   │   └── 📂 frontend/             # 前台元件
│   │   └── 📂 vendor/                   # 第三方套件視圖
│   │
│   ├── 📂 js/                           # JavaScript 檔案
│   │   ├── app.js                       # 主應用入口
│   │   ├── bootstrap.js                 # Bootstrap 配置
│   │   └── 📂 frontend/                 # 前台 JavaScript
│   │
│   ├── 📂 css/                          # 全局樣式
│   │   ├── about.css
│   │   ├── frontend.css
│   │   └── home.scss
│   │
│   └── 📂 sass/                         # SASS/SCSS 樣式
│       ├── app.scss                     # 主樣式
│       ├── frontend.scss                # 前台樣式
│       ├── _mixins.scss                 # SASS 混合巨集
│       └── _variables.scss              # SASS 變數
│
├── 🗂️ database/                         # 資料庫相關檔案
│   ├── database.sqlite                  # SQLite 資料庫檔案
│   │
│   ├── 📂 migrations/                   # 資料庫遷移檔案（版本控制）
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_08_12_091402_create_languages_table.php
│   │   ├── 2025_08_12_195945_create_news_category_table.php
│   │   ├── 2025_08_12_200124_create_news_category_desc_table.php
│   │   ├── 2025_08_31_184541_create_advert_tables.php
│   │   ├── 2025_08_31_194544_create_advert_category_tables.php
│   │   ├── 2025_09_02_192102_rename_sort_order_to_display_order_in_advert_table.php
│   │   ├── 2025_09_02_193425_rename_sort_order_to_display_order_in_advert_category_table.php
│   │   ├── 2025_09_02_194433_rename_sort_order_to_display_order_in_languages_table.php
│   │   ├── 2025_12_12_165649_create_news_tables.php
│   │   ├── 2025_12_12_165724_create_news_desc_tables.php
│   │   ├── 2026_01_11_005343_create_action_logs_tables.php
│   │   ├── 2026_01_19_030044_create_admin_roles_table.php
│   │   ├── 2026_01_19_030105_add_columns_to_users_table.php
│   │   ├── 2026_01_20_034903_add_parent_id_to_users_table.php
│   │   ├── 2026_01_21_025557_add_permissions_to_users_table.php
│   │   ├── 2026_01_31_164813_add_preferences_to_users_table.php
│   │   ├── 2026_02_15_015710_add_admin_system_settings_table.php
│   │   ├── 2026_03_02_225041_add_is_visible_home_to_news_table.php
│   │   ├── 2026_03_04_173908_add_desc_config_and_del_range_to_system_settings.php
│   │   ├── 2026_03_12_115745_add_meta_to_news.php
│   │   ├── 2026_03_24_014125_create_contacts_table.php
│   │   ├── 2026_03_24_014126_create_contact_replies_table.php
│   │   ├── 2026_05_08_024641_create_product_category_table.php
│   │   ├── 2026_05_08_024656_create_product_category_desc_table.php
│   │   └── 2026_05_14_011346_create_product_table.php
│   │
│   ├── 📂 factories/                    # 資料工廠（測試資料生成）
│   │   └── UserFactory.php              # 用戶工廠
│   │
│   └── 📂 seeders/                      # 資料庫播種器（初始資料）
│       ├── DatabaseSeeder.php           # 主播種器
│       ├── AdminSystemSettingSeeder.php # 系統設定播種器
│       └── MailSettingsSeeder.php       # 郵件設定播種器
│
├── 🗂️ storage/                          # 應用存儲檔案
│   ├── 📂 app/                          # 應用程式產生的檔案
│   │   ├── .gitignore
│   │   ├── 📂 private/                  # 私有檔案
│   │   ├── 📂 public/                   # 公開檔案
│   │   └── 📂 purifier/                 # HTML 淨化器快取
│   │
│   ├── 📂 framework/                    # Laravel 框架檔案
│   ├── 📂 logs/                         # 應用日誌
│   └── 📂 debugbar/                     # 調試工具欄快取
│
├── 🗂️ public/                           # Web 根目錄（公開可訪問）
│   ├── index.php                        # 應用入口檔案
│   ├── .htaccess                        # Apache 重寫規則
│   ├── web.config                       # IIS 配置
│   ├── robots.txt                       # 搜尋引擎爬蟲指令
│   ├── hello.txt                        # 測試檔案
│   ├── test.php                         # 測試檔案
│   │
│   ├── 📂 storage/                      # 公開存儲連結
│   │   ├── .gitignore
│   │   ├── 📂 admin/                    # 後台上傳檔案
│   │   ├── 📂 admins/                   # 管理員頭像
│   │   ├── 📂 adv/                      # 廣告圖片
│   │   ├── 📂 editor/                   # 編輯器圖片
│   │   ├── 📂 news/                     # 新聞圖片
│   │   ├── 📂 news_category/            # 新聞分類圖片
│   │   ├── 📂 product/                  # 產品圖片
│   │   ├── 📂 product_category/         # 產品分類圖片
│   │   └── 📂 uploads/                  # 其他上傳檔案
│   │
│   ├── 📂 css/                          # 編譯後的 CSS
│   ├── 📂 js/                           # 編譯後的 JavaScript
│   ├── 📂 fonts/                        # 字體檔案
│   ├── 📂 images/                       # 靜態圖片
│   ├── 📂 favicons/                     # 網站圖示
│   ├── 📂 template/                     # (推測) 模板資源
│   └── 📂 vendor/                       # 前端依賴（如 jQuery、Bootstrap 等）
│
├── 🗂️ tests/                            # 自動化測試檔案
│   ├── TestCase.php                     # 測試基類
│   ├── 📂 Feature/                      # 功能測試
│   └── 📂 Unit/                         # 單元測試
│
├── 🗂️ lang/                             # 多語言翻譯檔案
│   ├── 📂 en/                           # 英文翻譯
│   ├── 📂 zh-TW/                        # 繁體中文翻譯
│   └── 📂 vendor/                       # 第三方套件翻譯
│
├── 🗂️ vendor/                           # Composer 依賴套件（自動生成）
│   └── (包含 Laravel、第三方套件等)
│
├── 🗂️ node_modules/                     # Node.js 依賴套件（自動生成）
│   └── (包含 npm 套件)
│
├── 🗂️ test-image/                       # 測試圖片
│   ├── cookie-banner.jpg
│   ├── mooncake.jpg
│   ├── taste-pic1.jpg
│   └── ... (共 12 張測試圖片)
│
├── 🗂️ .git/                             # Git 版本控制
│
└── 🗂️ .vscode/                          # VS Code 編輯器配置
    └── launch.json                      # 調試配置
```

---

## 專案功能總結

這是一個 **多功能後台管理系統 + 前台商業網站** 的 Laravel 專案，主要功能包括：

### 後台管理系統
- 🔐 **用戶與權限管理** - 管理員帳號、角色和權限
- 📰 **新聞管理系統** - 分類、發布、編輯
- 📢 **廣告管理系統** - 廣告位置、分類、排期
- 🛍️ **產品管理系統** - 分類、SKU、庫存
- 💬 **聯絡管理** - 聯絡表單、回覆管理
- 🌍 **多語言支持** - 支援英文、繁體中文
- 📊 **系統日誌** - 操作記錄、審計追蹤
- ⚙️ **系統設定** - 網站配置、郵件設定

### 前台功能
- 🏠 **首頁展示** - 產品、新聞展示
- 🛍️ **產品展示** - 產品列表、詳情頁
- 📰 **新聞發布** - 新聞列表、詳情頁
- 📧 **聯絡表單** - 線上客服表單
- 👤 **用戶資料** - 個人資料編輯

### 技術棧
- **後端框架** - Laravel
- **前端** - Blade 模板、Vue 元件
- **UI 框架** - AdminLTE（後台）
- **構建工具** - Vite
- **資料庫** - SQLite + 遷移系統
- **測試** - PHPUnit

user-ask: @workspace

請針對這個 Laravel 專案產出「面試用架構分析文件」，內容必須包含：

## 1. 專案目錄結構
- 完整 tree 結構
- 每個資料夾用途

## 2. app/ 架構分析
- Controllers
- Models
- Services
- Helpers
- Providers
- Mail
- Traits

每一類都要包含：
- 負責的職責
- 檔案清單
- 每個檔案用途（逐一說明）

## 3. 系統運作流程
- Request → Controller → Service → Model → Response

## 4. 模組關係說明
- 各層之間如何互動

## 5. 不確定內容標記
- 若無法判斷請標註「推測」

輸出格式：Markdown

GitHub Copilot: 您已超過您的付費要求配額。我們已自動將您切換到 GPT-4.1，這是您的計劃所隨附的內容。[啟用其他付費進階要求](command:chat.enablePremiumOverages) 以繼續使用進階模型。以下是針對此 Laravel 專案的「面試用架構分析文件」：

---

# Laravel 專案架構分析文件

## 1. 專案目錄結構

```
cooookie/
├── app/                  # 應用程式核心目錄
│   ├── Helpers/          # 輔助工具（自訂輔助函式）
│   ├── Http/             # HTTP 請求相關（控制器、中介層、表單驗證）
│   │   ├── Controllers/  # 控制器（業務邏輯進入點）
│   │   ├── Middleware/   # 中介層（請求攔截/處理）
│   │   └── Requests/     # 表單請求驗證
│   ├── Listeners/        # 事件監聽器
│   ├── Mail/             # 郵件通知
│   ├── Models/           # Eloquent 資料模型
│   ├── Providers/        # 服務提供者（註冊服務/事件）
│   ├── Traits/           # Traits（可重用程式片段）
│   └── View/             # 視圖元件（推測）
├── bootstrap/            # 啟動與自動載入
├── config/               # 設定檔
├── database/             # 資料庫相關（migrations, seeders, factories）
├── lang/                 # 語系檔
├── public/               # 公開目錄（入口、靜態資源）
├── resources/            # 前端資源（views, js, css, sass）
├── routes/               # 路由定義
├── storage/              # 檔案儲存
├── tests/                # 測試
├── vendor/               # Composer 依賴
├── node_modules/         # npm 依賴
└── 其他設定與說明檔案
```

### 主要資料夾用途說明

- **app/**：應用程式主體，包含 MVC 架構核心、輔助工具、服務提供者等。
- **bootstrap/**：啟動設定與快取。
- **config/**：所有系統設定檔。
- **database/**：資料庫遷移、工廠、播種器。
- **lang/**：多語系翻譯檔。
- **public/**：網站入口與靜態資源。
- **resources/**：前端資源與 Blade 視圖。
- **routes/**：路由定義。
- **storage/**：檔案、快取、日誌等儲存。
- **tests/**：自動化測試。
- **vendor/**、**node_modules/**：依賴套件。

---

## 2. app/ 架構分析

### Controllers

- **職責**：負責接收 HTTP 請求、調用服務/模型、回傳 Response。
- **檔案清單與用途**：

#### app/Http/Controllers/
- `Controller.php`：所有控制器的基底類。
- `HomeController.php`：前台首頁邏輯。
- `ProfileController.php`：用戶個人資料管理。
- `UploadController.php`：檔案上傳處理。

#### app/Http/Controllers/Admin/
- `BaseAdminController.php`：後台控制器基底。
- `DashboardController.php`：後台儀表板。
- `ActionLogController.php`：操作日誌管理。
- `AdminUserController.php`：管理員帳號管理。
- `AdminRoleController.php`：角色權限管理。
- `LanguageController.php`：多語言管理。
- `NewsCategoryController.php`：新聞分類管理。
- `NewsController.php`：新聞管理。
- `AdvertCategoryController.php`：廣告分類管理。
- `AdvertController.php`：廣告管理。
- `ProductCategoryController.php`：產品分類管理。
- `ProductController.php`：產品管理。
- `ContactController.php`：聯絡表單管理。
- `SystemSettingController.php`：系統設定管理。
- `SystemLogController.php`：系統日誌管理。
- `ClearCacheController.php`：快取清除。

#### app/Http/Controllers/AdminAuth/
- `AuthenticatedSessionController.php`：後台登入/登出。
- `ConfirmablePasswordController.php`：密碼確認。
- `EmailVerificationNotificationController.php`：Email 驗證通知。
- `EmailVerificat
