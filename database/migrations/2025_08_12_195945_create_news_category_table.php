<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * 建立 news_category 主表
 * - cat_id 為主鍵（自增 unsigned big int）
 * - parent_id / parent_ids / super_id：用來存分類層級資訊（可選）
 * - is_visible / display_order：顯示與排序控制
 * - timestamps：created_at, updated_at
 *
 * 注意：此 migration 必須先於 news_category_desc 的 migration 執行，
 *       否則 desc 的 FK 建立會失敗（errno: 150）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_category', function (Blueprint $table) {
            // 指定引擎為 InnoDB（支援 FK）
            $table->engine = 'InnoDB';

            // 主鍵 cat_id（unsignedBigInteger auto-increment）
            $table->id('cat_id');

            // 上層分類（可為 null）
            $table->unsignedBigInteger('parent_id')->nullable()->index()->comment('上層分類 cat_id');

            // 父類 ID 字串（例如 "1,3,5"）
            $table->string('parent_ids')->nullable()->comment('上層 ID 串, 例如: 1,3,5');

            // 最上層分類 ID（可選）
            $table->unsignedBigInteger('super_id')->nullable()->comment('最上層分類 cat_id');

            // 是否顯示（布林）與顯示排序（數字越大越前）
            $table->boolean('is_visible')->default(true)->comment('是否顯示');
            $table->integer('display_order')->default(0)->comment('顯示排序，數字大者優先');

            // 建立/更新時間
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_category');
    }
};
