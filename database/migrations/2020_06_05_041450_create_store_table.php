<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store', function (Blueprint $table) {
            $table->increments('store_id')->comment('店面id');
            $table->string('store_name', 30)->comment('店面名称');
            $table->unsignedInteger('province_id')->comment('所在省份id');
            $table->unsignedInteger('city_id')->comment('所在城市id');
            $table->unsignedInteger('region_id')->comment('所在地区id');
            $table->string('address')->comment('店面详细地址');
            $table->unsignedInteger('company_id')->index()->comment('所属商户，对应公司表id');
            $table->string('describe', 200)->nullable()->comment('店面描述');
            $table->unsignedInteger('staff_id')->comment('店面联系人id，对应staff表id');
            $table->tinyInteger('check')->default(0)->comment('审核状态0未审核，1审核通过，2审核不通过');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store');
    }
}
