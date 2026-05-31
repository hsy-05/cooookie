<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立廣告區域分類代碼表
     * @return void
     */
    public function up(): void
    {
        Schema::create('advert_category', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_id')->autoIncrement();
            $table->string('cat_code', 50)->unique()->comment('分類代碼，例如 idx_banner');
            $table->string('cat_func_scope', 255)->nullable()->comment('功能範圍，例如 adv_img_url, adv_img_m_url');
            $table->longText('cat_params')->nullable()->comment('功能參數，存 JSON 結構');
            $table->integer('display_order')->default(0)->comment('排序');
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->timestamps();
        });
    }

    /**
     * 刪除廣告分類表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_category');
    }
};
