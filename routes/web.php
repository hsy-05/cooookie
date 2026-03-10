<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsController as FrontendNewsController;
use App\Http\Controllers\Frontend\AboutController;

/*
|--------------------------------------------------------------------------
| Backend (Admin) Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ActionLogController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\AdvertController;
use App\Http\Controllers\Admin\AdvertCategoryController;
use App\Http\Controllers\Admin\BaseAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ClearCacheController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\SystemLogController;

/*
|--------------------------------------------------------------------------
| Other Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use Faker\Provider\Base;

/*
|--------------------------------------------------------------------------
| Frontend Routes（前台）
|--------------------------------------------------------------------------
*/

// 前台首頁
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// 最新消息列表
Route::get('/news', [FrontendNewsController::class, 'index'])
    ->name('news.index');

// 最新消息分類列表
Route::get('/news/category/{category}', [FrontendNewsController::class, 'index'])
    ->name('news.category');

// 最新消息內頁（使用 Laravel 隱式模型綁定）
Route::get('/news/{news}', [FrontendNewsController::class, 'show'])
    ->name('news.show');

// 關於我們
Route::get('/about', [AboutController::class, 'index'])
    ->name('about');

/*
|--------------------------------------------------------------------------
| Profile（會員個人資料）
|--------------------------------------------------------------------------
*/
// Route::middleware('auth')->group(function () {

//     // 編輯個人資料頁
//     Route::get('/profile', [ProfileController::class, 'edit'])
//         ->name('profile.edit');

//     // 更新個人資料
//     Route::patch('/profile', [ProfileController::class, 'update'])
//         ->name('profile.update');

//     // 刪除帳號
//     Route::delete('/profile', [ProfileController::class, 'destroy'])
//         ->name('profile.destroy');
// });

/*
|--------------------------------------------------------------------------
| Authentication Routes（Laravel Breeze / Fortify）
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Admin Routes（後台）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin.theme']) // 加入 admin.theme 中介軟體
    ->prefix('admin') //路由前綴
    ->name('admin.')
    ->group(function () {
        // 後台首頁
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::post('clear-cache', [ClearCacheController::class, 'clearCache'])->name('clear.cache');

        // Roles & Users (假設管理員才能操作)
        Route::resource('roles', AdminRoleController::class);

        // 1. 顯示個人資料頁 (用既有的 edit 方法，邏輯已通)
        Route::get('admins/profile', [App\Http\Controllers\Admin\AdminUserController::class, 'profile'])
            ->name('admins.profile');

        // 2. 【新增】儲存個人資料 (專屬路由，避開 Resource 的 update 權限鎖)
        Route::put('admins/profile', [App\Http\Controllers\Admin\AdminUserController::class, 'updateProfile'])
            ->name('admins.updateProfile');

        Route::resource('admins', AdminUserController::class);
        Route::get('admins/{id}/impersonate', [AdminUserController::class, 'impersonate'])->name('admins.impersonate');


        /*
        |--------------------------------------------------------------------------
        | 語系管理
        |--------------------------------------------------------------------------
        */
        Route::resource('languages', LanguageController::class);

        /*
        |--------------------------------------------------------------------------
        | 系統參數設定
        |--------------------------------------------------------------------------
        */
        Route::get('system_settings', [SystemSettingController::class, 'index'])
            ->name('system_settings.index');

        // 系統紀錄頁面
        Route::get('system-logs', [SystemLogController::class, 'index'])
            ->name('admin.system.logs');

        // 使用 PUT 方法符合「更新」的語意，對應表單裡的 @method('PUT')
        Route::put('system_settings/update-all', [SystemSettingController::class, 'updateAll'])
            ->name('system_settings.update_all');


        /*
        |--------------------------------------------------------------------------
        | 操作紀錄 Logs
        |--------------------------------------------------------------------------
        */

        // 批次刪除（一定要放在 resource 之前）
        Route::delete('logs/batch', [ActionLogController::class, 'batchDestroy'])
            ->name('logs.batch_destroy');

        // 僅使用 index + destroy
        Route::resource('logs', ActionLogController::class)
            ->only(['index', 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | 最新消息分類
        |--------------------------------------------------------------------------
        */
        // 刪除封面圖片
        Route::post('news_category/delete-image/{category}', [NewsCategoryController::class, 'deleteImageField'])->name('news_category.delete-image');

        Route::resource('news_category', NewsCategoryController::class)
            ->parameters(['news_category' => 'category']);

        /*
        |--------------------------------------------------------------------------
        | 最新消息
        |--------------------------------------------------------------------------
        */

        // 批次刪除（一定要放在 resource 之前）
        Route::delete('news/batch', [NewsController::class, 'batchDestroy'])
            ->name('news.batch_destroy');

        // 刪除封面圖片
        Route::post('news/delete-image/{news}', [NewsController::class, 'deleteImageField'])->name('news.delete-image');

        Route::resource('news', NewsController::class);

        /*
        |--------------------------------------------------------------------------
        | 產品分類
        |--------------------------------------------------------------------------
        */
        Route::resource('product_category', ProductCategoryController::class);

        /*
        |--------------------------------------------------------------------------
        | 廣告分類
        |--------------------------------------------------------------------------
        */
        Route::resource('advert_category', AdvertCategoryController::class);

        /*
        |--------------------------------------------------------------------------
        | 廣告管理
        |--------------------------------------------------------------------------
        */
        // 刪除封面圖片
        Route::post('advert/delete-image/{advert}', [AdvertController::class, 'deleteImageField'])->name('advert.delete-image');

        Route::resource('advert', AdvertController::class);

        /*
        |--------------------------------------------------------------------------
        | 共用功能
        |--------------------------------------------------------------------------
        */

        // Summernote 圖片上傳
        Route::post('upload-editor-image', [UploadController::class, 'uploadEditorImage'])
            ->name('upload.image');

        // Summernote 圖片移除
        Route::post('delete-editor-image', [UploadController::class, 'deleteEditorImage']);


        // 【新增】取得編輯器系統參數設定 (供 summernote-init.js 使用)
        Route::get('editor-settings', [UploadController::class, 'getEditorSettings'])
            ->name('editor.settings');

        // AJAX：切換 boolean 狀態
        Route::post('toggle-boolean', [BaseAdminController::class, 'toggleBoolean'])
            ->name('toggle.boolean');
    });
