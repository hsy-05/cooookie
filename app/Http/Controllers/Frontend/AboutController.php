<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function index()
    {
        // --- 麵包屑與 Title 處理 ---
        $crumbs = [['text' => '關於我們', 'href' => route('about')]];

        // 呼叫父類別方法，自動處理全站共享變數
        $this->setBreadcrumbs($crumbs);

        return view('frontend.about');
    }
}
