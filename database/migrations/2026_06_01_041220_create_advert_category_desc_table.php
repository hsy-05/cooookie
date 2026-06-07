<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立廣告分類的多國語言稱呼表
     * @return void
     */
    public function up(): void
    {
        Schema::create('advert_category_desc', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_id')->index();
            $table->unsignedTinyInteger('lang_id')->index();
            $table->string('cat_name', 255);
            $table->timestamps();

            $table->unique(['lang_id', 'cat_id'], 'cat_desc_cat_id_lang_id_unique');
        });
    }

    /**
     * 刪除廣告分類多語系表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_category_desc');
    }
};
