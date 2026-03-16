<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 執行遷移：新增 SEO 相關欄位
     */
    public function up(): void
    {
        // 假設您的描述表名為 news_desc
        Schema::table('news_desc', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->comment('SEO 標題');
            $table->string('meta_description')->nullable()->comment('SEO 描述');
            $table->string('meta_keyword')->nullable()->comment('SEO 關鍵字');
            $table->string('seo_h1')->nullable()->comment('SEO H1 標籤');
        });
    }

    /**
     * 回復遷移
     */
    public function down(): void
    {
        Schema::table('news_desc', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keyword', 'seo_h1']);
        });
    }
};
