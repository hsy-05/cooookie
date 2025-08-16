<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * 建立 news_category_desc 多語系翻譯表
 * - 使用複合主鍵 (cat_id, lang_id)，不另外建立自增 id
 * - name/description/content 存放不同 lang 的文字內容
 * - 設定外鍵：
 *     cat_id -> news_category.cat_id (cascade on delete)
 *     lang_id -> language.lang_id (cascade on delete)  (請確保 language 表與該欄位存在)
 *
 * 注意：
 * - 此檔要在 news_category 與 language 表存在後才能成功建立。
 * - 若你的 language migration 會在之後建立，請先建立 language。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_category_desc', function (Blueprint $table) {
            // 指定引擎為 InnoDB（支援 FK）
            $table->engine = 'InnoDB';

            // 外鍵欄位（注意型態需與被參照欄位一致：unsignedBigInteger）
            $table->unsignedBigInteger('cat_id')->index()->comment('參照 news_category.cat_id');
            $table->unsignedBigInteger('lang_id')->index()->comment('language.lang_id');

            // 多語系欄位
            $table->string('name')->comment('分類名稱（各語系）');
            $table->string('description')->nullable()->comment('簡述（各語系）');
            $table->longText('content')->nullable()->comment('內文（各語系），可使用 CKEditor 編輯');

            $table->timestamps();

            // 使用複合主鍵 (cat_id, lang_id)
            $table->primary(['cat_id', 'lang_id']);

            // 外鍵約束
            // 注意：若 language 或 news_category 還沒建立，這裡會失敗（errno 150）
            $table->foreign('cat_id')
                  ->references('cat_id')->on('news_category')
                  ->onDelete('cascade');

            // $table->foreign('lang_id')
            //       ->references('lang_id')->on('language')
            //       ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_category_desc');
    }
};
