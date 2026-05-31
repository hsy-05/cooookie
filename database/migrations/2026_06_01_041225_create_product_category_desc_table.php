<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立商品分類語言別稱呼與資料表
     * @return void
     */
    public function up(): void
    {
        Schema::create('product_category_desc', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_id')->comment('分類ID');
            $table->unsignedTinyInteger('lang_id')->index()->comment('language.lang_id');
            $table->string('name', 255)->comment('分類名稱（各語系）');
            $table->string('description', 255)->nullable()->comment('簡述（各語系）');
            $table->longText('content')->nullable()->comment('內文（各語系），可使用 CKEditor 編輯');
            $table->timestamps();

            $table->primary(['cat_id', 'lang_id']);
        });
    }

    /**
     * 刪除產品分類多語系表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_desc');
    }
};
