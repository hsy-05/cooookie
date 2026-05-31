<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立最新消息實體清單表
     * @return void
     */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->unsignedInteger('news_id')->autoIncrement();
            $table->unsignedBigInteger('cat_id')->nullable()->index()->comment('news_category.cat_id');
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->boolean('is_visible_home')->default(false);
            $table->integer('display_order')->default(0)->comment('排序');
            $table->string('image_url', 255)->nullable()->comment('圖片路徑');
            $table->timestamps();
        });
    }

    /**
     * 刪除最新消息主表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
