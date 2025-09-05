
// 消息：列表與詳細頁
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show'); // 隱式綁定 news by PK
