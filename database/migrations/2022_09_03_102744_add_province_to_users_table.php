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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('province_id')->nullable()->comment('省份 ID');
            $table->integer('city_id')->nullable()->comment('市 ID');
            $table->integer('district_id')->nullable()->comment('区 ID');
            $table->string('province_name')->nullable()->comment("省");
            $table->string('city_name')->nullable()->comment("市");
            $table->string('district_name')->nullable()->comment("区");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
