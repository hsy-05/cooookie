<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\UploadController;
use App\Helpers\ContentHelper;
use App\Http\Controllers\Admin\AdvertController;
use App\Http\Controllers\Admin\AdvertCategoryController;

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsController as FrontendNewsController;



// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// 前台首頁
Route::get('/', [\App\Http\Controllers\Frontend\HomeController::class, 'index'])
    ->name('frontend.layouts.home');

// 消息：列表與詳細頁
Route::get('/news', [FrontendNewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [FrontendNewsController::class, 'show'])->name('news.show'); // 隱式綁定 news by PK


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

Auth::routes();

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('languages', LanguageController::class);            // 語言管理
    Route::resource('news_category', NewsCategoryController::class);  // 分類管理
    Route::resource('news', NewsController::class)->parameters([
        'news' => 'news' // 讓隱式綁定用 App\Models\News 的主鍵
    ]);

    // 廣告分類 CRUD
    Route::resource('advert_category', AdvertCategoryController::class)
        ->parameters(['advert_category' => 'advert_category']); // 讓隱式綁定用 cat_id

    // 廣告 CRUD（你先前已建立，放這裡備註）
    Route::resource('advert', AdvertController::class)
        ->parameters(['advert' => 'advert']);

    Route::post('upload-image', [App\Http\Controllers\UploadController::class, 'uploadImage']);
    // 通用 AJAX 路由，用於切換布林值狀態
    Route::post('toggle-boolean', [App\Http\Controllers\Admin\BaseAdminController::class, 'toggleBoolean'])->name('toggle.boolean');
});
