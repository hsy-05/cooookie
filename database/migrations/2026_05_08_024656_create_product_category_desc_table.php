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
        Schema::create('product_category_desc', function (Blueprint $table) {
             // 外鍵欄位（注意型態需與被參照欄位一致：unsignedBigInteger）
            $table->unsignedBigInteger('cat_id')->index()->comment('分類ID');
            $table->unsignedBigInteger('lang_id')->index()->comment('language.lang_id');

            // 多語系欄位
            $table->string('name')->comment('分類名稱（各語系）');
            $table->string('description')->nullable()->comment('簡述（各語系）');
            $table->longText('content')->nullable()->comment('內文（各語系），可使用 CKEditor 編輯');

            $table->timestamps();

            // 使用複合主鍵 (cat_id, lang_id)
            $table->primary(['cat_id', 'lang_id']);

            // 外鍵約束
            // 注意：若 language 或 product_category 還沒建立，這裡會失敗（errno 150）
            $table->foreign('cat_id')
                  ->references('cat_id')->on('product_category')
                  ->onDelete('cascade');

            // $table->foreign('lang_id')
            //       ->references('lang_id')->on('language')
            //       ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_desc');
    }
};
