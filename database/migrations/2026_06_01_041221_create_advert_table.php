<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立廣告實體資料與圖檔路徑表
     * @return void
     */
    public function up(): void
    {
        Schema::create('advert', function (Blueprint $table) {
            $table->unsignedBigInteger('adv_id')->autoIncrement();
            $table->unsignedBigInteger('cat_id')->index();
            $table->string('adv_img_url', 255)->nullable()->comment('電腦版圖片');
            $table->string('adv_img_m_url', 255)->nullable()->comment('手機版圖片');
            $table->string('adv_link_url', 255)->nullable()->comment('廣告連結');
            $table->integer('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * 刪除廣告主表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('advert');
    }
};
