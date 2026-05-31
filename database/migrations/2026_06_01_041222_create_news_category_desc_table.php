<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立最新消息分類的多國語言描述表
     * @return void
     */
    public function up(): void
    {
        Schema::create('news_category_desc', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_id')->primary()->comment('參照 news_category.cat_id');
            $table->unsignedTinyInteger('lang_id')->index()->comment('language.lang_id');
            $table->string('name', 255)->comment('分類名稱（各語系）');
            $table->string('description', 255)->nullable()->comment('簡述（各語系）');
            $table->longText('content')->nullable()->comment('內文（各語系），可使用 CKEditor 編輯');
            $table->timestamps();

            $table->unique(['cat_id', 'lang_id'], 'cat_desc_cat_id_lang_id_unique');
        });
    }

    /**
     * 刪除最新消息分類多語系表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('news_category_desc');
    }
};
