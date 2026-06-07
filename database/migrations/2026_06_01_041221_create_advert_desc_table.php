<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立廣告文字標題的多語系文字表
     * @return void
     */
    public function up(): void
    {
        Schema::create('advert_desc', function (Blueprint $table) {
            $table->unsignedBigInteger('adv_id')->index();
            $table->unsignedTinyInteger('lang_id');
            $table->string('adv_name', 100);
            $table->string('adv_subname', 150)->nullable();
            $table->text('adv_brief')->nullable();
        });
    }

    /**
     * 刪除廣告內容多語系表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_desc');
    }
};
