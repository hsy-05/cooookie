/**
 * AdminLTE Theme Engine
 * 修復說明：修正了局部顏色調整時會遺失「深色模式」狀態的問題。
 */
(function ($) {
    'use strict';

    const SELECTORS = {
        body: 'body',
        navbar: '.main-header',
        sidebar: '.main-sidebar',
        navSidebar: '.nav-sidebar',
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

    const DEFAULTS = {
        dark_mode: true, sidebar_collapse: false, nav_flat: false,
        sidebar_fixed: true, text_sm: false,
        navbar: 'navbar-white navbar-light', sidebar: 'sidebar-dark-primary', accent: 'accent-primary'
    };

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
     * 獲取當前 UI 上的所有配置值
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
     * 套用樣式主函數
     * @param {Object} c 配置物件
     * @param {Boolean} syncUI 是否同步更新畫面上表單元件
     */
    function applyStyles(c, syncUI = true) {
        const $body = $(SELECTORS.body);

        // 1. 布林值功能切換：優先從參數取值，若無則從參數的 pref_ 前綴取，最後才讀取 DEFAULTS
        $body.toggleClass('dark-mode', !!(c.dark_mode ?? c.pref_dark_mode ?? DEFAULTS.dark_mode));
        $body.toggleClass('sidebar-collapse', !!(c.sidebar_collapse ?? c.pref_sidebar_collapse ?? DEFAULTS.sidebar_collapse));
        $body.toggleClass('layout-fixed', !!(c.sidebar_fixed ?? c.pref_sidebar_fixed ?? DEFAULTS.sidebar_fixed));
        $body.toggleClass('text-sm', !!(c.text_sm ?? c.pref_text_sm ?? DEFAULTS.text_sm));
        $(SELECTORS.navSidebar).toggleClass('nav-flat', !!(c.nav_flat ?? c.pref_nav_flat ?? DEFAULTS.nav_flat));

        // 2. 顏色類別切換
        const updateClass = ($el, regex, newClass) => {
            $el.removeClass((i, className) => (className.match(regex) || []).join(' ')).addClass(newClass);
        };

        const navClass = c.navbar_color || c.navbar || DEFAULTS.navbar;
        updateClass($(SELECTORS.navbar), /(^|\s)(navbar-|bg-)\S+/g, 'main-header navbar navbar-expand ' + navClass);

        const sideClass = c.sidebar_theme || c.sidebar || DEFAULTS.sidebar;
        updateClass($(SELECTORS.sidebar), /(^|\s)sidebar-(dark|light)-\S+/g, sideClass);

        const accentClass = c.accent_color || c.accent || '';
        updateClass($body, /(^|\s)accent-\S+/g, accentClass);

        // 3. UI 同步 (只有在切換「快速主題」或「重置」時需要)
        if (syncUI) {
            const ins = SELECTORS.inputs;
            $(ins.darkMode).prop('checked', !!(c.dark_mode ?? c.pref_dark_mode));
            $(ins.sidebarCollapse).prop('checked', !!(c.sidebar_collapse ?? c.pref_sidebar_collapse));
            $(ins.navFlat).prop('checked', !!(c.nav_flat ?? c.pref_nav_flat));
            $(ins.sidebarFixed).prop('checked', !!(c.sidebar_fixed ?? c.pref_sidebar_fixed));
            $(ins.textSm).prop('checked', !!(c.text_sm ?? c.pref_text_sm));
            $(ins.navbarVariant).val(navClass);
            $(ins.sidebarVariant).val(sideClass);
            $(ins.accentColor).val(accentClass);
        }

        // --- 針對頁籤頂部線條 card-outline-tabs 的連動處理 ---
        const $targetCard = $('.card-outline-tabs');

        if ($targetCard.length > 0) {
            // 移除舊顏色，保留結構
            $targetCard.removeClass((i, className) => {
                return (className.match(/(^|\s)card-(?!outline)\S+/g) || []).join(' ');
            });

            // 取得新顏色，預設 primary
            let newCardColor = 'card-primary';
            if (accentClass && accentClass.startsWith('accent-')) {
                newCardColor = accentClass.replace('accent-', 'card-');
            }

            // 套用新顏色，頂部粗線就會變色
            $targetCard.addClass(newCardColor + ' card-outline card-outline-tabs');
        }
    }

    /** 綁定事件 */
    function bindEvents() {
        const ins = SELECTORS.inputs;

        // 當開關類、下拉選單類變動時，直接讀取當前「所有」UI 狀態重新套用
        $(`${ins.darkMode}, ${ins.sidebarCollapse}, ${ins.navFlat}, ${ins.sidebarFixed}, ${ins.textSm}, ${ins.navbarVariant}, ${ins.sidebarVariant}, ${ins.accentColor}`).on('change', function() {
            // 如果是手動調整顏色，取消快速主題的選中狀態
            if ($(this).is('select')) {
                $(ins.themePreset).prop('checked', false).parent().removeClass('active');
            }
            applyStyles(getCurrentUIConfig(), false);
        });

        // 快速主題
        $(ins.themePreset).on('change', function() {
            const val = $(this).val();
            if (THEME_PRESETS[val]) applyStyles(THEME_PRESETS[val], true);
        });

        // 重置
        $(ins.resetBtn).on('click', (e) => {
            e.preventDefault();
            if(confirm('確定要還原為預設配置嗎？')) applyStyles(DEFAULTS, true);
        });
    }

    $(function () {
        // 初始化時，如果 window.UserPrefs 存在則使用，否則使用預設
        const initialConfig = (window.UserPrefs && Object.keys(window.UserPrefs).length > 0) ? window.UserPrefs : DEFAULTS;
        applyStyles(initialConfig, true);
        bindEvents();
    });
})(jQuery);
