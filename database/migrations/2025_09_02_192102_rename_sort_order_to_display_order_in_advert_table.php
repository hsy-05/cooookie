<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSortOrderToDisplayOrderInAdvertTable extends Migration
{
    public function up(): void
    {
        // 確保你已安裝 doctrine/dbal，才能使用 renameColumn
        Schema::table('advert', function (Blueprint $table) {
            $table->renameColumn('sort_order', 'display_order');
        });
    }

    public function down(): void
    {
        Schema::table('advert', function (Blueprint $table) {
            $table->renameColumn('display_order', 'sort_order');
        });
    }
}
