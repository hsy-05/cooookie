<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('news_desc', function (Blueprint $table) {
            // 產品外鍵，連結到 news 表的 news_id
            $table->unsignedInteger('news_id')->primary()->comment('news.news_id');

            // 語言 ID，連結到 languages 表的 lang_id
            $table->unsignedMediumInteger('lang_id')->index()->comment('languages.lang_id');

            // 標題欄位，使用 VARCHAR，長度限制為 255
            $table->string('title')->nullable()->comment('標題（語系）');

            // 產品描述內容，使用 TEXT 取代 LONGTEXT
            $table->text('content')->nullable()->comment('內容（語系）');

            // 產品 ID 和語言 ID 必須唯一，避免重複資料
            $table->unique(['news_id', 'lang_id']);

            // 自動產生 created_at 和 updated_at 時間戳
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_desc'); // 刪除 news_desc 表
    }
};
