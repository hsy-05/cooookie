<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            // 主鍵 cat_id（unsignedBigInteger auto-increment）
            $table->id('news_id');
            $table->unsignedBigInteger('cat_id')->nullable()->index()->comment('news_category.id');
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->integer('display_order')->default(0)->comment('排序');
            $table->string('image')->nullable()->comment('封面圖檔名');
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
