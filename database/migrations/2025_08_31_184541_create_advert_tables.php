<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 🔹 廣告主表
        Schema::create('advert', function (Blueprint $table) {
            $table->id('adv_id');
            $table->unsignedBigInteger('cat_id');
            $table->string('adv_img_url')->nullable()->comment('電腦版圖片');
            $table->string('adv_img_m_url')->nullable()->comment('手機版圖片');
            $table->string('adv_link_url')->nullable()->comment('廣告連結');
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('is_visible')->default(1);
            $table->timestamps();

            $table->foreign('cat_id')->references('cat_id')->on('advert_category')->onDelete('cascade');
        });

        // 🔹 廣告語系表
        Schema::create('advert_desc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adv_id');
            $table->string('lang_id', 10);
            $table->string('adv_name', 100);
            $table->string('adv_subname', 150)->nullable();
            $table->text('adv_brief')->nullable();
            $table->timestamps();

            $table->foreign('adv_id')->references('adv_id')->on('advert')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_desc');
        Schema::dropIfExists('advert');
    }
};
