/**
 * AdminLTE 介面預覽引擎
 * 用途：專用於「網站管理員」設定頁面，提供即時動態預覽，不負責初始載入。
 */
(function ($) {
    'use strict';

    // 介面選擇器：統一管理，方便日後維護改名
    const SELECTORS = {
        body: 'body',
        navbar: '.main-header',
        sidebar: '.main-sidebar',
        navSidebar: '.nav-sidebar',
        customTabs: '.card-outline-tabs', // 客製化頁籤
        inputs: {
            darkMode: '#pref_dark_mode',
            sidebarCollapse: '#pref_sidebar_collapse',
            navFlat: '#pref_nav_flat',
            sidebarFixed: '#pref_sidebar_fixed',
            textSm: '#pref_text_sm',
            navbarVariant: '#pref_navbar_variant',
            sidebarVariant: '#pref_sidebar_variant',
            accentColor: '#pref_accent_color',
            themePreset: 'input[name="theme_preset"]',
            resetBtn: '#js-reset-theme'
        }
    };

    // 系統預設值：當使用者按下「重置」時套用
    const DEFAULTS = {
        dark_mode: false,
        sidebar_collapse: false,
        nav_flat: false,
        sidebar_fixed: true,
        text_sm: false,
        navbar: 'navbar-white navbar-light',
        sidebar: 'sidebar-dark-primary',
        accent: 'accent-primary'
    };

    // 快速主題預設檔
    const THEME_PRESETS = {
        // 科技極夜
        'cyber': {
            dark_mode: true, nav_flat: true, text_sm: true, sidebar_fixed: true,
            navbar: 'navbar-dark navbar-primary', sidebar: 'sidebar-dark-primary', accent: 'accent-primary'
        },
        // 皇家紫
        'purple': {
            dark_mode: false, nav_flat: false, text_sm: false, sidebar_fixed: true,
            navbar: 'navbar-dark navbar-purple', sidebar: 'sidebar-dark-purple', accent: 'accent-purple'
        },
        // 玫紅色
        'pink': {
            dark_mode: false, nav_flat: false, text_sm: false, sidebar_fixed: true,
            navbar: 'navbar-dark navbar-pink', sidebar: 'sidebar-dark-pink', accent: 'accent-pink'
        },
        // 森林
        'forest': {
            dark_mode: false, nav_flat: true, text_sm: false, sidebar_fixed: true,
            navbar: 'navbar-dark navbar-success', sidebar: 'sidebar-dark-success', accent: 'accent-success'
        },
        // 海洋深潛
        'ocean': {
            dark_mode: false, nav_flat: false, text_sm: false, sidebar_fixed: true,
            navbar: 'navbar-dark navbar-info', sidebar: 'sidebar-light-info', accent: 'accent-info'
        },
        // 海軍藍
        'navy': {
            dark_mode: false, nav_flat: true, text_sm: true, sidebar_fixed: true,
            navbar: 'navbar-dark navbar-navy', sidebar: 'sidebar-light-navy', accent: 'accent-navy'
        },
        // 經典暖陽
        'classic': {
            dark_mode: true, nav_flat: false, text_sm: false, sidebar_fixed: true,
            navbar: 'navbar-light navbar-warning', sidebar: 'sidebar-dark-warning', accent: 'accent-warning'
        },
        // 橄欖綠
        'olive': {
            dark_mode: false, nav_flat: false, text_sm: false, sidebar_fixed: true,
            navbar: 'navbar-dark navbar-olive', sidebar: 'sidebar-dark-olive', accent: 'accent-olive'
        },
    };

    /**
     * 獲取目前畫面上表單的設定值
     */
    function getCurrentUIConfig() {
        const ins = SELECTORS.inputs;
        return {
            dark_mode: $(ins.darkMode).is(':checked'),
            sidebar_collapse: $(ins.sidebarCollapse).is(':checked'),
            nav_flat: $(ins.navFlat).is(':checked'),
            sidebar_fixed: $(ins.sidebarFixed).is(':checked'),
            text_sm: $(ins.textSm).is(':checked'),
            navbar: $(ins.navbarVariant).val(),
            sidebar: $(ins.sidebarVariant).val(),
            accent: $(ins.accentColor).val()
        };
    }

    /**
     * 核心功能：將設定值套用到當前 DOM 元素上（即時預覽）
     * @param {Object} c 配置物件
     * @param {Boolean} syncUI 是否要連動更新表單上的勾選狀態
     */
    function previewStyles(c, syncUI = true) {
        const $body = $(SELECTORS.body);

        // 1. 切換開關類 (Checkbox / Boolean)
        // !! 是為了強制轉成布林值，防呆確保不會傳入奇怪的字串
        $body.toggleClass('dark-mode', !!c.dark_mode);
        $body.toggleClass('sidebar-collapse', !!c.sidebar_collapse);
        $body.toggleClass('layout-fixed', !!c.sidebar_fixed);
        $body.toggleClass('text-sm', !!c.text_sm);
        $(SELECTORS.navSidebar).toggleClass('nav-flat', !!c.nav_flat);

        // 2. 切換顏色類 (Select / Class)
        // 專業做法：先用 Regex 移除舊的相關 Class，再塞新的，避免 Class 堆疊
        const updateClass = ($el, regex, newClass) => {
            if (!$el.length) return;
            $el.removeClass((i, className) => (className.match(regex) || []).join(' ')).addClass(newClass);
        };

        // Navbar 預覽
        const navClass = c.navbar_color || c.navbar || DEFAULTS.navbar;
        updateClass($(SELECTORS.navbar), /(^|\s)(navbar-|bg-)\S+/g, 'main-header navbar navbar-expand ' + navClass);

        // Sidebar 預覽
        const sideClass = c.sidebar_theme || c.sidebar || DEFAULTS.sidebar;
        updateClass($(SELECTORS.sidebar), /(^|\s)sidebar-(dark|light)-\S+/g, sideClass);

        const accentClass = c.accent_color || c.accent || '';
        updateClass($body, /(^|\s)accent-\S+/g, accentClass);

        // 3. --- 客製化頁籤 (.card-outline-tabs) 連動處理 ---
        const $targetCard = $(SELECTORS.customTabs);
        if ($targetCard.length > 0) {
            // 計算規則：accent-primary -> card-primary
            const newCardColor = accentClass.replace('accent-', 'card-');

            // 移除舊顏色，但確保保留 card-outline 與 card-outline-tabs 結構
            $targetCard.removeClass((i, className) => {
                const matches = className.match(/(^|\s)card-(?!outline)\S+/g);
                return matches ? matches.join(' ') : '';
            }).addClass(newCardColor);
        }

        // 4. 表單狀態同步
        if (syncUI) {
            const ins = SELECTORS.inputs;
            $(ins.darkMode).prop('checked', !!c.dark_mode);
            $(ins.sidebarCollapse).prop('checked', !!c.sidebar_collapse);
            $(ins.navFlat).prop('checked', !!c.nav_flat);
            $(ins.sidebarFixed).prop('checked', !!c.sidebar_fixed);
            $(ins.textSm).prop('checked', !!c.text_sm);
            $(ins.navbarVariant).val(navClass);
            $(ins.sidebarVariant).val(sideClass);
            $(ins.accentColor).val(accentClass);
        }
    }

    /**
     * 事件綁定：監聽操作行為
     */
    function bindPreviewEvents() {
        const ins = SELECTORS.inputs;

        // 監聽所有輸入項
        $(`${ins.darkMode}, ${ins.sidebarCollapse}, ${ins.navFlat}, ${ins.sidebarFixed}, ${ins.textSm}, ${ins.navbarVariant}, ${ins.sidebarVariant}, ${ins.accentColor}`).on('change', function() {
            // 若手動變更細節，取消快速主題的選取狀態
            if (!$(this).is('input[name="theme_preset"]')) {
                $(ins.themePreset).prop('checked', false).parent().removeClass('active');
            }
            previewStyles(getCurrentUIConfig(), false);
        });

        // 快速主題切換
        $(ins.themePreset).on('change', function() {
            const val = $(this).val();
            if (THEME_PRESETS[val]) {
                previewStyles(THEME_PRESETS[val], true);
            }
        });

        // 重置
        $(ins.resetBtn).on('click', function(e) {
            e.preventDefault();
            if(confirm('確定要將預覽畫面還原為預設配置嗎？')) {
                previewStyles(DEFAULTS, true);
            }
        });
    }

    /**
     * 初始化：當 DOM 載入完成後執行
     */
    $(function () {
        // 專業邏輯：
        // 1. 我們不呼叫 previewStyles(initialConfig)，避免與 PHP 產生的 Class 衝突。
        // 2. 但為了確保「頁籤」在頁面載入時擁有正確的初始 Class，我們可以執行一次輕量級的同步。
        const currentAccent = $(SELECTORS.body).attr('class').match(/accent-\S+/);
        if (currentAccent) {
            const initialCardColor = currentAccent[0].replace('accent-', 'card-');
            $(SELECTORS.customTabs).addClass(initialCardColor);
        }

        bindPreviewEvents();
    });

})(jQuery);
