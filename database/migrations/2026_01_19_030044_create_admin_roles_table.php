<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('角色名稱');
            $table->string('description')->nullable()->comment('描述');
            // 使用 JSON 儲存該角色擁有的權限 Key，例如 ["news.view", "news.create"]
            $table->json('permissions')->nullable()->comment('權限列表');
            // 超級管理員標記，1=是 (不受限)，0=否
            $table->boolean('is_system')->default(0)->comment('是否為系統保留(超級管理員)');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_roles');
    }
};
