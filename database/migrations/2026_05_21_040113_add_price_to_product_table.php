<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 執行遷移修改
     *
     * 用途：在現有的 product 資料表中追加價格欄位，並加上防呆預設值與結構順序
     */
    public function up(): void
    {
        Schema::table('product', function (Blueprint $table) {
            // integer 代表整數（適合大部分台幣計價官網）
            // default(0) 是第一道防呆，確保沒填時預設為 0 元，不讓資料庫報錯
            // after('image_url') 代表將這個新欄位放在資料表的 image_url 欄位後面，方便進資料庫檢視
            $table->integer('price')->default(0)->after('image_url')->comment('產品定價');
        });
    }

    /**
     * 復原遷移修改
     *
     * 用途：如果未來需要還原此步驟，系統會自動把 price 欄位移除
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            // 刪除剛才新增的價格欄位
            $table->dropColumn('price');
        });
    }
};
