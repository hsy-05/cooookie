<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;

/**
 * 基礎控制器
 * 所有的前端 Controller 都繼承此類別，用來處理通用的資料邏輯
 */

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * 儲存麵包屑資料的陣列
     */
    protected array $breadcrumbs = [];

    /**
     * 統一處理全站的麵包屑與 SEO 標題
     * 這樣開發者只需要在 Controller 定義路徑，剩下的「組合」與「傳遞」都由系統自動完成
     * * @param array $crumbs 傳入當前頁面的路徑資訊，例如 [['text' => '最新消息', 'href' => '/news']]
     * @return void
     */
    protected function setBreadcrumbs(array $crumbs): void
    {
        // 自動將「首頁」加入陣列的最前面，確保每個頁面的麵包屑起點一致，不用手動重複寫
        $this->breadcrumbs = array_merge([
            ['text' => 'Home', 'href' => url('/')]
        ], $crumbs);

        // 處理 SEO 網頁標題 (Title) 的邏輯：
        // a. array_column: 從麵包屑陣列中只取出「文字」部分。
        // b. array_reverse: 將順序反轉（例如：首頁 > 消息 -> 變為 消息 > 首頁），符合搜尋引擎優先顯示重點關鍵字的習慣。
        // c. array_filter: 把「Home」字樣從標題中去掉，讓標題更簡潔精準。
        // d. implode: 用底線「 _ 」將剩餘的文字串接起來。
        $titleTextArray = array_column($this->breadcrumbs, 'text');
        $reversedArray = array_reverse($titleTextArray);
        $filteredArray = array_filter($reversedArray, fn($text) => $text !== 'Home');

        $pageTitle = implode('_', $filteredArray);

        // 3. 獲取當前頁面標題（最後一個麵包屑文字），作為網頁內的 <h1>
        $currentTitle = end($crumbs)['text'] ?? config('site.site_name');

        // 將處理好的資料「共享」給所有的前端頁面 (.blade.php)
        View::share([
            'breadcrumbs'  => $this->breadcrumbs,
            'pageTitle'    => $pageTitle ?: config('site.site_name'),
            'currentTitle' => $currentTitle // 這給 <h1> 使用
        ]);
    }
}
