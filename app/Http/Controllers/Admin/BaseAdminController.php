<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BaseAdminController extends Controller
{
    protected $pageTitle = '後台管理';

    /**
     * 統一輸出 view，並自動帶入 pageTitle
     */
    protected function view($view, $data = [])
    {
        return view($view, array_merge([
            'pageTitle' => $this->pageTitle,
        ], $data));
    }
}
