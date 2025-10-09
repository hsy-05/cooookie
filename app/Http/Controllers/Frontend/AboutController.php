<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function index()
    {
        $content = [
            "title" => "關於老井",
            "intro" => "考古資料記載距今150萬年前，周口店升起了華人的第一把火，用火炙烤食物經一代代的演化...",
            "quality" => "每月SGS評核檢測嚴格把關餐點品質給顧客安心的感動餐點",
            "service" => "追求顧客每一次用餐的笑容回憶給顧客溫暖感動服務",
            "workforce" => "愛護夥伴職涯的永續發展與栽培給員工感心相待的感動職場",
            "closing" => "一段溫飽您胃‧溫暖您心的餐飲旅程，精緻和風細膩，延伸料理景致..."
        ];

        return view('frontend.about', compact('content'));
    }
}
