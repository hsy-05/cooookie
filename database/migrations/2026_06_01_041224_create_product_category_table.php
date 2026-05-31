<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立產品類別階層樹狀表
     * @return void
     */
    public function up(): void
    {
        Schema::create('product_category', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_id')->autoIncrement();
            $table->unsignedBigInteger('parent_id')->nullable()->index()->comment('上層分類 cat_id');
            $table->string('parent_ids', 255)->nullable()->comment('上層 ID 串, 例如: 1,3,5');
            $table->unsignedBigInteger('super_id')->nullable()->comment('最上層分類 cat_id');
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->integer('display_order')->default(0)->comment('顯示排序，數字大者優先');
            $table->string('image_url', 255)->nullable()->comment('圖片路徑');
            $table->timestamps();
        });
    }

    /**
     * 刪除產品分類表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category');
    }
};
