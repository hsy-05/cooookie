<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立商品介紹、詳情與前端 SEO 的多國語言描述表
     * @return void
     */
    public function up(): void
    {
        Schema::create('product_desc', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->comment('關聯產品 ID');
            $table->unsignedTinyInteger('lang_id')->index()->comment('關聯語言 ID');
            $table->string('title', 120)->nullable()->comment('語系標題');
            $table->string('description', 255)->nullable()->comment('語系簡述');
            $table->text('content')->nullable()->comment('語系內容(HTML)');
            $table->string('meta_title', 255)->nullable()->comment('SEO 標題');
            $table->string('meta_description', 255)->nullable()->comment('SEO 描述');
            $table->string('meta_keyword', 255)->nullable()->comment('SEO 關鍵字');
            $table->string('seo_h1', 255)->nullable()->comment('SEO H1');
            $table->timestamps();

            $table->primary(['product_id', 'lang_id']);
            $table->unique(['product_id', 'lang_id'], 'news_desc_news_id_lang_id_unique');
        });
    }

    /**
     * 刪除產品內文多語系表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('product_desc');
    }
};
