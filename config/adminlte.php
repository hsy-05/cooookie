<?php

return [
/*
    |--------------------------------------------------------------------------
    | Title (瀏覽器分頁標題)
    |--------------------------------------------------------------------------
    | 用途：設定顯示在瀏覽器最上方標籤頁的文字。
    */

    'title' => '後台管理',               // 預設標題文字
    'title_prefix' => '',               // 標題前綴（例如：「後台 | 」，顯示會變成「後台 | 後台管理」）
    'title_postfix' => ' - XXX有限公司', // 標題後綴（例如:「 - XX有限公司」，顯示會變成「後台管理 - XX有限公司」）

    /*
    |--------------------------------------------------------------------------
    | Favicon (網頁小圖示)
    |--------------------------------------------------------------------------
    | 用途：設定顯示在瀏覽器標籤頁標題旁邊的 16x16 小圖示。
    */

    'use_ico_only' => true,      // 是否只使用 .ico 格式的圖檔
    'use_full_favicon' => false, // 是否使用完整的一組圖示（包含各類行動裝置用的圖示設定）

    /*
    |--------------------------------------------------------------------------
    | Google Fonts (字體)
    |--------------------------------------------------------------------------
    | 用途：是否載入 Google 線上字體。如果你的主機無法連外網，才需要關閉。
    */

    'google_fonts' => [
        'allowed' => true,       // 是否允許載入 Google Fonts
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo (左上角商標)
    |--------------------------------------------------------------------------
    | 用途：後台進入後，左上角顯示的品牌標誌。
    */

    'logo' => '<b>XX有限公司</b>', // Logo 旁邊顯示的文字，支援 HTML 標籤（如 <b> 粗體）
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png', // Logo 圖片路徑（相對於 public 資料夾）
    'logo_img_class' => 'brand-image img-circle elevation-3', // Logo 圖片的 CSS 類別（img-circle 為圓形，elevation-3 為陰影）
    'logo_img_xl' => null,            // 寬螢幕模式下是否使用較大的 Logo 圖檔（設為 null 則統一使用上面那張）
    'logo_img_xl_class' => 'brand-image-xs', // 寬螢幕 Logo 的額外樣式
    'logo_img_alt' => 'Admin Logo',   // 圖片無法顯示時的替代文字（SEO 用）

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo (登入頁商標)
    |--------------------------------------------------------------------------
    | 用途：在登入、註冊頁面顯示的 Logo。若關閉(false)，則預設使用上面的 Logo 設定。
    */

    'auth_logo' => [
        'enabled' => false, // 是否啟用獨立的登入頁 Logo
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png', // 登入頁 Logo 路徑
            'alt' => 'Auth Logo',   // 替代文字
            'class' => '',          // 額外樣式類別
            'width' => 50,          // 寬度
            'height' => 50,         // 高度
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation (讀取動畫)
    |--------------------------------------------------------------------------
    | 用途：當網頁還在載入時，顯示一個過渡動畫（俗稱 Loading 畫面）。
    */

    'preloader' => [
        'enabled' => false,      // 是否開啟讀取動畫（新手開發建議先設 false，才不會每次重整都要等動畫）
        'mode' => 'fullscreen',  // 顯示模式：'fullscreen' 滿版蓋住全螢幕，'cwrapper' 僅蓋住內容區
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png', // 動畫中顯示的圖片
            'alt' => 'AdminLTE Preloader Image', // 圖片替代文字
            'effect' => 'animation__shake',      // 動畫效果（如：animation__shake 震動、animation__wobble 搖晃）
            'width' => 60,   // 圖片寬度
            'height' => 60,  // 圖片高度
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    // 控制用戶菜單的顯示
    'usermenu_enabled' => true, // 如果設置為 true，則顯示用戶菜單，false 則隱藏用戶菜單

    // 控制用戶菜單中的頭部區域是否顯示
    'usermenu_header' => false, // 設置為 true 以顯示頭部區域，false 則隱藏

    // 設定用戶菜單頭部區域的 CSS 類別，用於自定義背景顏色等
    'usermenu_header_class' => 'bg-primary', // 頭部背景顏色為 "primary" 顏色（通常為藍色）。可根據需要修改為其他顏色，如 'bg-success', 'bg-danger' 等

    // 控制是否顯示用戶的頭像（圖片）
    'usermenu_image' => true, // 如果設置為 true，則顯示用戶頭像，false 則不顯示

    // 控制是否顯示用戶描述（如職位名稱、角色等）
    'usermenu_desc' => false, // 設置為 true 以顯示用戶描述，false 則隱藏

    // [設定 1]：靜態個人資料 URL (設為 null 以停用)
    // 原因：我們已在 Admin.php 模型中使用動態方法 adminlte_profile_url()，
    // 為避免設定與程式碼衝突(Override)，此處明確設為 null。
    'profile_url' => null,

    // [設定 2]：控制用戶菜單中的個人資料鏈接是否顯示
    // 啟用後，AdminLTE 會自動呼叫模型(Admin.php)中的 adminlte_profile_url() 方法。
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout (佈局)
    |--------------------------------------------------------------------------
    */

    'layout_topnav' => null,        // 是否啟動「頂部導覽模式」。若設為 true，側邊選單會消失，全部移到最上方。
    'layout_boxed' => null,         // 是否使用「框線佈局」。若設為 true，畫面不會填滿螢幕，會縮在中間一個方框內。
    'layout_fixed_sidebar' => true, // 側邊欄固定。捲動頁面時，左側選單會留在原位不會跟著捲走。
    'layout_fixed_navbar' => true,  // 頂部導航固定。捲動頁面時，最上方那條會一直留著。
    'layout_fixed_footer' => null,  // 頁腳固定。讓最下方的版權宣告一直卡在螢幕底部。
    'layout_dark_mode' => true,     // 啟用暗黑模式。注意：這會讓全站背景變黑。
    'sidebar_user_panel' => true,   // 是否在左側選單上方顯示使用者的小頭像和名字。


    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes (身分驗證頁面樣式)
    |--------------------------------------------------------------------------
    */

    'classes_auth_card'   => 'card-outline card-primary', // 登入方框的外框樣式。card-primary 會在上方有一條藍色線。
    'classes_auth_header' => '', // 登入框標題的額外樣式。
    'classes_auth_body'   => '', // 登入框內容區的額外樣式。
    'classes_auth_footer' => '', // 登入框底部(忘記密碼那區)的樣式。
    'classes_auth_icon'   => '', // 輸入框小圖示的顏色設定。
    'classes_auth_btn'    => 'btn-flat btn-primary', // 登入按鈕樣式。btn-flat 代表不要圓角，btn-primary 是藍色。

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes (管理面板類別)
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body'             => '', // 套用到全網頁 <body> 標籤的額外 CSS 類別
    'classes_brand'            => '', // 左上角商標 (Logo) 區塊的樣式
    'classes_brand_text'       => '', // 商標文字的樣式
    'classes_content_wrapper'  => '', // 主要內容顯示區域的背景樣式
    'classes_content_header'   => '', // 頁面標題區 (Breadcrumbs 那區) 的樣式
    'classes_content'          => '', // 頁面內容容器的樣式
    'classes_sidebar'          => 'sidebar-dark-primary elevation-4', // 側邊欄顏色主題 (深色底+藍色高亮) 與陰影深度 (層級4)
    'classes_sidebar_nav'      => '', // 側邊欄導航選單本身的額外樣式
    'classes_topnav'           => 'navbar-white navbar-light', // 頂部導航條顏色 (白底亮色)
    'classes_topnav_nav'       => 'navbar-expand', // 頂部導航選單的排列行為 (預設為水平擴展)
    'classes_topnav_container' => 'container',    // 頂部導航條的容器樣式 (限制最大寬度)
    'classes_footer'           => 'mt-5',         // 頁尾 (Footer) 樣式，mt-5 代表距離上方內容 3rem 的間距

    /*
    |--------------------------------------------------------------------------
    | Sidebar (側邊欄設定)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini'                            => 'lg',    // 當螢幕小於指定尺寸時，自動將側邊欄縮小為僅顯示圖示 (小圖示模式)
    'sidebar_collapse'                        => false,   // 設定進入頁面時，側邊欄是否預設為「收合」狀態
    'sidebar_collapse_auto_size'              => false,   // 是否在自動縮小時根據內容調整大小
    'sidebar_collapse_remember'               => true,    // 瀏覽器是否記住使用者上次手動收合或打開選單的狀態
    'sidebar_collapse_remember_no_transition' => true,    // 當頁面重新整理並載入「記憶中」的狀態時，不顯示縮放動畫 (直接跳到結果)
    'sidebar_scrollbar_theme'                 => 'os-theme-light', // 側邊欄捲軸的主題樣式 (亮色)
    'sidebar_scrollbar_auto_hide'             => 'l',     // 捲軸自動隱藏的行為 (預設離開時隱藏)
    'sidebar_nav_accordion'                   => false,   // 手風琴模式。設為 true 則點開新選單組時，會自動關閉其他已展開的選單組
    'sidebar_nav_animation_speed'             => 200,     // 點擊展開或收合選單時的動畫流暢速度 (毫秒)

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (右側側邊欄/控制欄)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar'                     => false,            // 是否啟用右側側邊欄功能
    'right_sidebar_icon'                => 'fas fa-cogs',   // 觸發右側側邊欄開關的圖示 (預設為齒輪)
    'right_sidebar_theme'               => 'dark',           // 右側側邊欄的顏色主題 (深色)
    'right_sidebar_slide'               => true,            // 顯示方式：滑動式效果 (滑入畫面)
    'right_sidebar_push'                => true,            // 顯示方式：推擠式效果 (將原內容向左擠)
    'right_sidebar_scrollbar_theme'     => 'os-theme-light', // 右側側邊欄捲軸的主題樣式
    'right_sidebar_scrollbar_auto_hide' => 'l',              // 右側側邊欄捲軸的自動隱藏行為

    /*
    |--------------------------------------------------------------------------
    | URLs (後台路徑設定)
    |--------------------------------------------------------------------------
    */

    // 是否強制使用 Laravel 的「路由名稱 (route name)」
    // [說明] 設為 false 代表你直接寫網址路徑 (如 /admin)；設為 true 代表你要寫路由別名 (如 admin.index)。
    'use_route_url' => false,

    // [用途] 登入後預設導向的「後台首頁」網址
    // [說明] 當你點選後台左上角的 Logo 時，也會連到這個網址。
    'dashboard_url' => '/admin',

    // [用途] 登出的執行網址
    // [說明] 指向 Breeze 預設的登出路徑。
    'logout_url' => 'logout',

    // [用途] 登入頁面的網址
    'login_url' => 'login',

    // [用途] 註冊頁面的網址
    // [說明] 你之前已經決定關閉，所以這裡設為 null。這會讓頁面上的「註冊」連結消失。
    'register_url' => null,

    // [用途] 忘記密碼頁面的網址
    // [說明] 如果你不想讓管理員自己重設密碼，設為 null。
    'password_reset_url' => null,

    // [用途] 是否禁止深色模式專用的後端路由
    // [說明] 我們之前討論過，因為你不打算用深色模式，可以保持 false 或改 true 都不影響外觀。
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling (資源加載模式)
    |--------------------------------------------------------------------------
    */

    // [用途] 是否使用 Laravel 的打包工具 (Mix 或 Vite)
    // [實話實說]
    // - 如果你是一個新手，且沒有特別去跑「npm run dev」，請保持為 false。
    // - 設為 false 的話，AdminLTE 會直接去 public 資料夾找編譯好的檔案。
    // - 設為 vite 或 mix，它會自動幫你產生對應的 HTML 標籤。
    'laravel_asset_bundling' => false,

    // [用途] 當上述設定開啟時，CSS 檔案的主檔案路徑
    'laravel_css_path' => 'css/app.css',

    // [用途] 當上述設定開啟時，JS 檔案的主檔案路徑
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => false,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        // [
        //     'type' => 'sidebar-menu-search',
        //     'text' => 'search',
        // ],
        // [
        //     'text' => 'blog',
        //     'url' => 'admin/blog',
        //     'can' => 'manage-blog',
        // ],
        [
            'text' => '廣告列表',
            'icon' => 'fas fa-fw fa-image',
            'submenu' => [
                [
                    'text' => '廣告列表',
                    // 'route' => 'admin.advert',   // 優先使用 route（如果你有命名路由）
                    'url' => 'admin/advert',
                    'active' => ['regex:@^admin/advert($|/)@'], // 可用陣列 / 通配符，*代表匹配所有後綴
                ],
                [
                    'text' => '廣告分類',
                    // 'route' => 'admin.advert_category',   // 優先使用 route（如果你有命名路由）
                    'url' => 'admin/advert_category',
                    'active' => ['regex:@^admin/advert_category($|/)@'], // 可用陣列 / 通配符
                ],
            ],
        ],
        [
            'text' => '消息列表',
            'icon' => 'fas fa-fw fa-newspaper',
            'submenu' => [
                [
                    'text' => '最新消息',
                    // 'route' => 'admin.news',   // 優先使用 route（如果你有命名路由）
                    'url' => 'admin/news',
                    'active' => ['regex:@^admin/news($|/)@'], // 可用陣列 / 通配符，*代表匹配所有後綴
                    'can'  => 'news.view',
                ],
                [
                    'text' => '消息分類',
                    // 'route' => 'admin.news',   // 優先使用 route（如果你有命名路由）
                    'url' => 'admin/news_category',
                    'active' => ['regex:@^admin/news_category($|/)@'], // 可用陣列 / 通配符
                    'can'  => 'news_category.view',
                ],
            ],
        ],
        [
            'text' => '產品列表',
            'icon' => 'fas fa-fw fa-box',
            'submenu' => [
                [
                    'text' => '產品',
                    // 'route' => 'admin.product',   // 優先使用 route（如果你有命名路由）
                    'url' => 'admin/product',
                    'active' => ['regex:@^admin/product($|/)@'], // 可用陣列 / 通配符，*代表匹配所有後綴
                ],
                [
                    'text' => '產品分類',
                    // 'route' => 'admin.product',   // 優先使用 route（如果你有命名路由）
                    'url' => 'admin/product_category',
                    'active' => ['regex:@^admin/product_category($|/)@'], // 可用陣列 / 通配符
                ],
            ],
        ],
        [
            'text' => '客服管理',
            'icon' => 'fas fa-fw fa-user-tie',
            'submenu' => [
                [
                    'text' => '聯絡我們',
                    // 'route' => 'admin.contact',   // 優先使用 route（如果你有命名路由）
                    'url' => 'admin/contact',
                    'active' => ['regex:@^admin/contact($|/)@'], // 可用陣列 / 通配符，*代表匹配所有後綴
                ],
            ],
        ],
        // [
        //     'text' => 'pages',
        //     'url' => 'admin/pages',
        //     'icon' => 'far fa-fw fa-file',
        //     'label' => 4,
        //     'label_color' => 'success',
        // ],
        ['header' => 'account_settings'],
        [
            'text' => '管理設定',
            'icon' => 'fas fa-cog',
            'submenu' => [
                [
                    'text' => '語系設定',
                    'url'  => 'admin/languages',
                    'active' => ['regex:@^admin/languages($|/)@'], // 可用陣列 / 通配符
                ],
            ],
        ],
        [
            'text' => '權限設定',
            'icon' => 'fas fa-fw fa-users',
            'submenu' => [
                [
                    'text' => '操作紀錄',
                    'url'  => 'admin/logs',
                    'active' => ['regex:@^admin/logs($|/)@'], // 可用陣列 / 通配符
                    'can'  => 'logs.view',
                ],
                [
                    'text' => '角色管理',
                    'url'  => 'admin/roles',
                    'active' => ['regex:@^admin/roles($|/)@'], // 可用陣列 / 通配符
                    'can'  => 'roles.view',
                ],
                [
                    'text' => '管理員設定',
                    'url'  => 'admin/admins',
                    'active' => ['regex:@^admin/admins($|/)@'], // 可用陣列 / 通配符
                    'can'  => 'admins.view',
                ],
            ],
        ],
        [
            'text' => '系統管理(僅開發者可見)',
            'icon' => 'fas fa-fw fa-users-cog',
            'submenu' => [
                [
                    'text' => '系統紀錄',
                    'url'  => 'admin/system-logs',
                    // 'icon' => 'fas fa-bug',
                    'active' => ['regex:@^admin/system-logs($|/)@'], // 可用陣列 / 通配符
                    'can'  => 'system',
                ],
            ],
        ],
        // [
        //     'text' => 'change_password',
        //     'url' => 'admin/settings',
        //     'icon' => 'fas fa-fw fa-lock',
        // ],
        // [
        //     'text' => 'multilevel',
        //     'icon' => 'fas fa-fw fa-share',
        //     'submenu' => [
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //             'submenu' => [
        //                 [
        //                     'text' => 'level_two',
        //                     'url' => '#',
        //                 ],
        //                 [
        //                     'text' => 'level_two',
        //                     'url' => '#',
        //                     'submenu' => [
        //                         [
        //                             'text' => 'level_three',
        //                             'url' => '#',
        //                         ],
        //                         [
        //                             'text' => 'level_three',
        //                             'url' => '#',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //         ],
        //     ],
        // ],
        // ['header' => 'labels'],
        // [
        //     'text' => 'important',
        //     'icon_color' => 'red',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'warning',
        //     'icon_color' => 'yellow',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'information',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 選單過濾器 (Menu Filters)
    |--------------------------------------------------------------------------
    | 用途：在選單顯示出來「之前」，透過這些類別來處理選單的權限、連結與外觀。
    | 建議：身為新手，這些設定保持原樣即可，不要隨意刪除。
    */

    'filters' => [
        // 1. [權限守門員] GateFilter
        // 用途：檢查你是否有權限看到這個選單。
        // 例如：你在選單設定了 'can' => 'manage-cookies'，如果當前管理員沒權限，這條選單會直接消失。
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,

        // 2. [網址轉換員] HrefFilter
        // 用途：負責處理選單的連結 (url 或 route)。
        // 例如：它會把你在設定檔寫的 'url' => 'admin/cookies' 變成真正的超連結。
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,

        // 3. [搜尋過濾員] SearchFilter
        // 用途：如果你有開啟選單搜尋功能，它負責幫你找出符合關鍵字的選單。
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,

        // 4. [啟用狀態員] ActiveFilter
        // 用途：判斷現在哪一個選單是「啟動中 (Active)」。
        // 效果：如果你現在在「餅乾清單」頁面，它會讓左邊的「餅乾清單」選單變色，讓你知到現在在哪一頁。
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,

        // 5. [樣式處理員] ClassesFilter
        // 用途：處理選單的 CSS 類別。
        // 例如：你在選單設定了 'icon_color' => 'red'，它負責把顏色套用上去。
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,

        // 6. [語言翻譯員] LangFilter
        // 用途：負責多國語言轉換。
        // 效果：如果你設定了 'text' => 'menu.cookies'，它會自動去語言檔幫你翻成「餅乾管理」。
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,

        // 7. [自定義資料員] DataFilter
        // 用途：處理選單中的自定義屬性 (data-attributes)。
        // 效果：讓你可以幫選單標籤加上像是 data-id="123" 這種資訊。
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | 自定義全域樣式
    |--------------------------------------------------------------------------
    | 用途：在這裡載入你自己寫的 CSS 檔案，這些檔案會套用到後台的「每一頁」。
    */
    'stylesheets' => [
        [
            'type'     => 'css',   // [用途] 指定檔案類型為 CSS
            'asset'    => true,    // [用途] 是否使用 Laravel 的 asset() 函式（true 代表檔案在你的 public 資料夾內）
            'location' => 'css/admin-custom.css', // [用途] 檔案的路徑，實際位置在 public/css/admin-custom.css
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        // 你自定義的通用 JS
        'CustomCommon' => [
            'active' => true, // [用途] 是否啟用。設為 true，每一頁都會載入。
            'files' => [
                [
                    'type'     => 'js',
                    'asset'    => true, // 檔案在 public 內
                    'location' => 'js/admin/common.js', // 位置：public/js/admin/common.js
                ],
            ],
        ],

        // Datatables (強大表格工具)
        'Datatables' => [
            'active' => false, // [用途] 是否啟用。建議用到該功能的頁面再單獨開啟。
            'files' => [
                [
                    'type'     => 'js',
                    'asset'    => false, // [用途] 設為 false 代表使用下方的網址(CDN)，而不從本機讀取
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],

        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        // Sweetalert2 (漂亮彈窗)
        'Sweetalert2' => [
            'active' => true, // 只有在需要刪除確認或提示時才開啟
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame 模式設定 (多標籤頁模式)
    |--------------------------------------------------------------------------
    | 用途：讓後台像瀏覽器一樣，點選選單時在右側開啟新的 Tab，而不是直接跳轉頁面。
    | 建議：除非你的客戶明確要求「要在後台同時開很多視窗」，否則保持預設或不使用。
    */

    'iframe' => [
        // 預設開啟的標籤頁
        'default_tab' => [
            'url'   => null,  // [用途] 後台一登入時，預設要自動開啟哪一個頁面的網址
            'title' => null, // [用途] 該預設分頁標籤上要顯示的文字 (例如: 控制台)
        ],

        // 標籤頁上的控制按鈕
        'buttons' => [
            'close'           => true, // [用途] 是否顯示「關閉目前分頁」的按鈕
            'close_all'       => true, // [用途] 是否顯示「全部關閉」按鈕
            'close_all_other' => true, // [用途] 是否顯示「關閉其他所有分頁」按鈕
            'scroll_left'     => true, // [用途] 分頁太多時，是否顯示「向左捲動」標籤欄的按鈕
            'scroll_right'    => true, // [用途] 分頁太多時，是否顯示「向右捲動」標籤欄的按鈕
            'fullscreen'      => true, // [用途] 是否顯示「全螢幕」按鈕，讓內容區域放大到整個螢幕
        ],

        // 其他進階設定
        'options' => [
            'loading_screen'    => 1000, // [用途] 切換頁面時「讀取中」畫面顯示的時間(毫秒)。1000 = 1秒。
            'auto_show_new_tab' => true, // [用途] 點選選單時，是否自動跳轉到新開的那個標籤頁
            'use_navbar_items'  => true, // [用途] 是否允許把導覽列(頂部)的連結也用分頁模式開啟
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
