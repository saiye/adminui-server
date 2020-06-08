<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company', function (Blueprint $table) {
            $table->increments('company_id')->comment('商户id');
            $table->string('company_name', 50)->comment('商户名称');
            $table->integer('state_id')->default(0)->comment('所在国家');
            $table->tinyInteger('proportion')->default(100)->comment('分成比例');
            $table->tinyInteger('status')->default(0)->comment('商户状态1正常，2禁封');
            $table->tinyInteger('check')->default(0)->comment('审核状态0未审核，1审核通过，2审核不通过');
            $table->string('reason',100)->default('')->comment('不通过原因');
            $table->integer('staff_id')->default(0)->comment('公司联系人id，对应staff表id');
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
        Schema::dropIfExists('business');
    }
}
