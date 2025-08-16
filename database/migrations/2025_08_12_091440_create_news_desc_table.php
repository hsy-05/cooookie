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
        Schema::create('news_desc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('news_id')->index()->comment('news.id');
            $table->unsignedBigInteger('lang_id')->index()->comment('language.lang_id');
            $table->string('title')->default('')->comment('標題（語系）');
            $table->longText('content')->nullable()->comment('內容（語系）');
            $table->unique(['news_id', 'lang_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_desc');
    }
};
