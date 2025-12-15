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
            // 主鍵 news_id (使用 INT 取代 BIGINT)
            $table->increments('news_id'); // 自動遞增的 INT 主鍵

            // 類別 ID，對應到 category 表的 cat_id，使用 MEDIUMINT
            $table->mediumInteger('cat_id')->nullable()->index()->comment('news_category.cat_id');

            // 是否顯示，布林值，預設為 true
            $table->boolean('is_visible')->default(true)->comment('是否顯示');

            // 排序欄位，使用 INT，預設為 0
            $table->integer('display_order')->default(0)->comment('排序');

            // 圖片檔名，使用 VARCHAR，長度限制為 255
            $table->string('image', 255)->nullable()->comment('圖片路徑');

            // 自動產生 created_at 和 updated_at 時間戳
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news'); // 刪除 news 表
    }
};
