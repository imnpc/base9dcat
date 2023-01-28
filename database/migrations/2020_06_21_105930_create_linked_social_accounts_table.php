<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linked_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户 ID');
            $table->string('provider_id');
            $table->string('provider_name');
            $table->string('unionid')->unique()->nullable();
            $table->text('token')->nullable()->comment('第三方登录 token');
            $table->text('message')->nullable()->comment('第三方登录接口返回信息');
            $table->timestamps();
            $table->comment('第三方登录');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('linked_social_accounts');
    }
};
