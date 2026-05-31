<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立商品資訊實體表
     * @return void
     */
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->autoIncrement();
            $table->unsignedBigInteger('cat_id')->nullable()->index()->comment('關聯分類 ID');
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->boolean('is_visible_home')->default(false)->comment('是否顯示於首頁');
            $table->integer('display_order')->default(0)->comment('排序');
            $table->string('image_url', 255)->nullable()->comment('圖片路徑');
            $table->integer('price')->default(0)->comment('產品定價');
            $table->timestamps();
        });
    }

    /**
     * 刪除產品介紹主表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
