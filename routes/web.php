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

/*
|--------------------------------------------------------------------------
| Other Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;

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

// 最新消息內頁（使用 Laravel 隱式模型綁定）
Route::get('/news/{news}', [FrontendNewsController::class, 'show'])
    ->name('news.show');

// 關於我們
Route::get('/about', [AboutController::class, 'index'])
    ->name('about');

/*
|--------------------------------------------------------------------------
| Dashboard（登入後首頁，Laravel 預設）
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Profile（會員個人資料）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // 編輯個人資料頁
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    // 更新個人資料
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    // 刪除帳號
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

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
Route::middleware(['auth', 'verified'])
    ->prefix('admin') //路由前綴
    ->name('admin.')
    ->group(function () {
        // 後台首頁
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Roles & Users (假設管理員才能操作)
        Route::resource('roles', AdminRoleController::class);

        Route::resource('users', AdminUserController::class);

        /*
        |--------------------------------------------------------------------------
        | 語系管理
        |--------------------------------------------------------------------------
        */
        Route::resource('languages', LanguageController::class);

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
        Route::resource('news_category', NewsCategoryController::class)
            ->parameters([
                'news_category' => 'category'
            ]);
        Route::resource('news_category', NewsCategoryController::class)
            ->parameters(['news_category' => 'category']);
        /*
        |--------------------------------------------------------------------------
        | 最新消息
        |--------------------------------------------------------------------------
        */
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
        Route::resource('advert', AdvertController::class);

        /*
        |--------------------------------------------------------------------------
        | 共用功能
        |--------------------------------------------------------------------------
        */

        // Summernote 圖片上傳
        Route::post('upload-image', [UploadController::class, 'uploadImage'])
            ->name('upload.image');

        // AJAX：切換 boolean 狀態（啟用 / 停用）
        Route::post('toggle-boolean', [BaseAdminController::class, 'toggleBoolean'])
            ->name('toggle.boolean');
    });
