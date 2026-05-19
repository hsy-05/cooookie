<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 產品模組整合遷移檔
 * 用途：建立產品主表 (product) 與產品多語系描述表 (product_desc)
 * 包含專業索引設定與外鍵約束
 */
return new class extends Migration
{
    /**
     * 執行遷移
     */
    public function up(): void
    {
        // 1. 建立產品主表
        Schema::create('product', function (Blueprint $table) {
            // 設定主鍵：PRIMARY KEY -> product_id
            $table->increments('product_id');

            // 分類 ID：設定索引名為 news_cat_id_index (符合你的舊有習慣)
            $table->mediumInteger('cat_id')->nullable()->comment('關聯分類 ID');
            $table->index('cat_id', 'news_cat_id_index');

            // 狀態與顯示控制
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->boolean('is_visible_home')->default(false)->comment('是否顯示於首頁');

            // 排序與圖片
            $table->integer('display_order')->default(0)->comment('排序');
            $table->string('image_url', 255)->nullable()->comment('圖片路徑');

            $table->timestamps();
        });

        // 2. 建立產品描述表
        Schema::create('product_desc', function (Blueprint $table) {
            /**
             * 索引設定：
             * PRIMARY KEY -> [product_id, lang_id] (複合主鍵)
             */
            $table->unsignedInteger('product_id')->comment('關聯產品 ID');
            $table->unsignedMediumInteger('lang_id')->comment('關聯語言 ID');

            // 定義複合主鍵
            $table->primary(['product_id', 'lang_id']);

            /**
             * 內容欄位
             */
            $table->string('title', 120)->nullable()->comment('語系標題');
            $table->string('description', 255)->nullable()->comment('語系簡述');
            $table->text('content')->nullable()->comment('語系內容(HTML)');

            /**
             * SEO 相關欄位
             */
            $table->string('meta_title')->nullable()->comment('SEO 標題');
            $table->string('meta_description')->nullable()->comment('SEO 描述');
            $table->string('meta_keyword')->nullable()->comment('SEO 關鍵字');
            $table->string('seo_h1')->nullable()->comment('SEO H1');

            /**
             * 額外索引設定：
             * news_desc_lang_id_index -> lang_id
             * news_desc_news_id_lang_id_unique -> [product_id, lang_id]
             */
            $table->index('lang_id', 'news_desc_lang_id_index');
            $table->unique(['product_id', 'lang_id'], 'news_desc_news_id_lang_id_unique');

            $table->timestamps();

            /**
             * 防呆設計：外鍵約束
             * 當 product 被刪除時，自動清理對應的描述資料
             */
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('product')
                  ->onDelete('cascade');
        });
    }

    /**
     * 復原遷移 (注意刪除順序)
     */
    public function down(): void
    {
        Schema::dropIfExists('product_desc');
        Schema::dropIfExists('product');
    }
};
