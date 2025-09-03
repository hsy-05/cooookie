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

        // 🔹 廣告分類表
        Schema::create('advert_category', function (Blueprint $table) {
            // 主鍵 cat_id（unsignedBigInteger auto-increment）
            $table->id('cat_id');
            $table->string('cat_code', 50)->unique()->comment('分類代碼，例如 idx_banner');
            $table->string('cat_func_scope')->nullable()->comment('功能範圍，例如 adv_img_url, adv_img_m_url');
            $table->json('cat_params')->nullable()->comment('功能參數，存 JSON 結構');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->timestamps();
        });

        // 🔹 廣告分類語系表
        Schema::create('advert_category_desc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cat_id');
            $table->unsignedBigInteger('lang_id');
            $table->string('cat_name');

            $table->timestamps();

            $table->foreign('cat_id')->references('cat_id')->on('advert_category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_category_desc');
        Schema::dropIfExists('advert_category');
    }
};
