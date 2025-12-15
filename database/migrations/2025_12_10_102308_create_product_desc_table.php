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
        Schema::create('product_desc', function (Blueprint $table) {
            // 外鍵，連結到 product 表的 product_id
            $table->unsignedInteger('product_id')->primary()->comment('product.product_id');

            // 語言 ID，連結到 languages 表的 lang_id
            $table->unsignedMediumInteger('lang_id')->index()->comment('languages.lang_id');

            // 標題欄位，使用 VARCHAR，長度限制為 255
            $table->string('title', 120)->nullable()->comment('標題（語系）');

            // 新增 description 欄位，為 VARCHAR(255)
            $table->string('description', 255)->nullable()->comment('描述（語系）');

            // 描述內容，使用 TEXT 取代 LONGTEXT
            $table->text('content')->nullable()->comment('內容（語系）');

            //  ID 和語言 ID 必須唯一，避免重複資料
            $table->unique(['product_id', 'lang_id']);

            // 自動產生 created_at 和 updated_at 時間戳
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_desc'); // 刪除 product_desc 表
    }
};
