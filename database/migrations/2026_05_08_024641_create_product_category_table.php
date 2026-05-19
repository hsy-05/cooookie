<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_category', function (Blueprint $table) {
            // 主鍵 cat_id（unsignedBigInteger auto-increment）
            $table->id('cat_id');

            // 上層分類（可為 null）
            $table->unsignedBigInteger('parent_id')->nullable()->index()->comment('上層分類 cat_id');

            // 父類 ID 字串（例如 "1,3,5"）
            $table->string('parent_ids')->nullable()->comment('上層 ID 串, 例如: 1,3,5');

            // 最上層分類 ID（可選）
            $table->unsignedBigInteger('super_id')->nullable()->comment('最上層分類 cat_id');

            // 是否顯示（布林）與顯示排序（數字越大越前）
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->integer('display_order')->default(0)->comment('顯示排序，數字大者優先');

            // 圖片檔名，使用 VARCHAR，長度限制為 255
            $table->string('image_url', 255)->nullable()->comment('圖片路徑');

            // 建立/更新時間
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category');
    }
};
