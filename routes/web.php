<?php

use Illuminate\Support\Facades\Route;

/**
 * --------------------------------------------------------------------------
 * 引入控制器 (Controllers)
 * --------------------------------------------------------------------------
 * 將前台與後台的命名空間分開，並對名稱重複的控制器進行別名設定 (as)
 * 這樣在下面寫路由時會更直覺，不會搞混。
 */

// 前台控制器
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsController as FrontendNewsController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\ContactController as FrontendContactController;

// 後台 (Admin) 控制器
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ActionLogController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdvertController;
use App\Http\Controllers\Admin\AdvertCategoryController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ClearCacheController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;

// 共用工具控制器
use App\Http\Controllers\UploadController;
use App\Http\Controllers\Admin\BaseAdminController;

/**
 * --------------------------------------------------------------------------
 * 前台路由 (Frontend Routes)
 * --------------------------------------------------------------------------
 */

// 網站首頁
Route::get('/', [HomeController::class, 'index'])->name('home');

// 最新消息模組：使用群組管理，方便未來擴充參數（如每頁顯示數量）
Route::group(['prefix' => 'news', 'as' => 'news.'], function () {
    // 消息列表頁
    Route::get('/', [FrontendNewsController::class, 'index'])->name('index');
    // 分類過濾頁面（讓網址看起來更具 SEO 友善度）
    Route::get('/category/{category}', [FrontendNewsController::class, 'index'])->name('category');
    // 消息內頁
    Route::get('/{news}', [FrontendNewsController::class, 'show'])->name('show');
});

// 產品模組：延續最新消息的優良結構
Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    // 產品列表頁 (支援分類過濾)
    Route::get('/', [FrontendProductController::class, 'index'])->name('index');
    Route::get('/category/{category}', [FrontendProductController::class, 'index'])->name('category');

    // 產品內頁
    Route::get('/{product}', [FrontendProductController::class, 'show'])->name('show');
});

// 關於我們
Route::get('/about', [AboutController::class, 'index'])->name('about');

// 專門用來預覽聯絡信件樣式的測試路由
Route::get('/preview-contact-mail', [FrontendContactController::class, 'previewMail'])->name('preview.contact.mail');

// 聯絡我們：對應前台表單提交與 reCAPTCHA 驗證
Route::group(['prefix' => 'contact', 'as' => 'contact.'], function () {
    Route::get('/', [FrontendContactController::class, 'index'])->name('index');
    // 表單提交路由，JavaScript 串接 API 時請對應此路徑
    Route::post('/store', [FrontendContactController::class, 'store'])->name('store');
});

/**
 * --------------------------------------------------------------------------
 * 認證系統 (Laravel Breeze)
 * --------------------------------------------------------------------------
 */
require __DIR__ . '/auth.php';

/**
 * --------------------------------------------------------------------------
 * 後台管理系統 (Admin Routes)
 * --------------------------------------------------------------------------
 * middleware 解釋：
 * - auth: 必須登入
 * - verified: 電子郵件必須已驗證
 * - admin.theme: 處理 AdminLTE 主題設定的中介軟體
 */
Route::middleware(['auth', 'verified', 'admin.theme'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // 後台儀表板首頁
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // 系統管理：清除快取（開發與維護時常用）
        Route::post('clear-cache', [ClearCacheController::class, 'clearCache'])->name('clear.cache');

        /**
         * 權限與帳號管理
         */
        Route::resource('roles', AdminRoleController::class);

        // 個人資料管理：特別獨立出來，不走一般的 Resource 權限鎖
        Route::get('admins/profile', [AdminUserController::class, 'profile'])->name('admins.profile');
        Route::put('admins/profile', [AdminUserController::class, 'updateProfile'])->name('admins.updateProfile');

        // 管理員 CRUD
        Route::resource('admins', AdminUserController::class);
        // 身分切換功能：方便開發者測試不同權限帳號
        Route::get('admins/{id}/impersonate', [AdminUserController::class, 'impersonate'])->name('admins.impersonate');

        /**
         * 系統設定與語系
         */
        Route::resource('languages', LanguageController::class);

        Route::get('system_settings', [SystemSettingController::class, 'index'])->name('system_settings.index');
        Route::put('system_settings/update-all', [SystemSettingController::class, 'updateAll'])->name('system_settings.update_all');

        // 伺服器與操作紀錄 (僅讀取與刪除)
        Route::get('system-logs', [SystemLogController::class, 'index'])
            ->name('system.logs')
            ->middleware('admin.perm:system-logs.view');
        Route::delete('logs/batch', [ActionLogController::class, 'batchDestroy'])->name('logs.batch_destroy');
        Route::resource('logs', ActionLogController::class)->only(['index', 'destroy']);

        /**
         * 內容管理 (Content Management)
         * 注意：自定義動作 (如 batch, delete-image) 必須放在 Resource 之前，避免被識別為 ID
         */

        // 聯絡單管理
        Route::delete('contact/batch', [AdminContactController::class, 'batchDestroy'])->name('contact.batch_destroy');
        Route::resource('contact', AdminContactController::class);

        // 最新消息分類
        Route::post('news_category/delete-image/{item}', [NewsCategoryController::class, 'deleteImageField'])->name('news_category.delete-image');
        Route::resource('news_category', NewsCategoryController::class)->parameters(['news_category' => 'item']);

        // 最新消息
        Route::delete('news/batch', [NewsController::class, 'batchDestroy'])->name('news.batch_destroy');
        Route::post('news/delete-image/{item}', [NewsController::class, 'deleteImageField'])->name('news.delete-image');
        Route::resource('news', NewsController::class)->parameters(['news' => 'item']);

        // 產品分類
        Route::post('product_category/delete-image/{category}', [ProductCategoryController::class, 'deleteImageField'])->name('product_category.delete-image');
        Route::resource('product_category', ProductCategoryController::class)->parameters(['product_category' => 'category']);

        // 產品
        Route::delete('product/batch', [ProductController::class, 'batchDestroy'])->name('product.batch_destroy');
        Route::post('product/delete-image/{item}', [ProductController::class, 'deleteImageField'])->name('product.delete-image');
        Route::resource('product', ProductController::class)->parameters(['product' => 'item']);

        // 廣告管理
        Route::post('advert/delete-image/{advert}', [AdvertController::class, 'deleteImageField'])->name('advert.delete-image');
        Route::resource('advert', AdvertController::class);

        //廣告分類
        Route::resource('advert_category', AdvertCategoryController::class)
            ->middleware('admin.perm:system.advert_category.view');

        /**
         * 共用功能與編輯器 (Summernote) 支援
         */
        Route::group(['as' => 'tools.'], function () {
            // Summernote 圖片上傳與移除
            Route::post('upload-editor-image', [UploadController::class, 'uploadEditorImage'])->name('upload.image');
            Route::post('delete-editor-image', [UploadController::class, 'deleteEditorImage'])->name('delete.image');

            // 取得編輯器設定參數 (供 summernote-init.js  初始化使用)
            Route::get('editor-settings', [UploadController::class, 'getEditorSettings'])->name('editor.settings');

            // 全域開關切換 (AJAX)
            Route::post('toggle-boolean', [BaseAdminController::class, 'toggleBoolean'])->name('toggle.boolean');
        });
    });
