<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立最新消息核心內容與前端 SEO 多語系管理表
     * @return void
     */
    public function up(): void
    {
        Schema::create('news_desc', function (Blueprint $table) {
            $table->unsignedInteger('news_id')->comment('news.news_id');
            $table->unsignedTinyInteger('lang_id')->index()->comment('languages.lang_id');
            $table->string('title', 120)->comment('標題（語系）');
            $table->string('description', 255)->nullable()->comment('描述（語系）');
            $table->text('content')->nullable()->comment('內容（語系）');
            $table->string('meta_title', 255)->nullable()->comment('SEO 標題');
            $table->string('meta_description', 255)->nullable()->comment('SEO 描述');
            $table->string('meta_keyword', 255)->nullable()->comment('SEO 關鍵字');
            $table->string('seo_h1', 255)->nullable()->comment('SEO H1 標籤');

            $table->primary(['news_id', 'lang_id']);
            $table->unique(['news_id', 'lang_id']);
        });
    }

    /**
     * 刪除最新消息內容多語系表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('news_desc');
    }
};
