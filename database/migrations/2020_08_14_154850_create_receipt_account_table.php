<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * 收款账户
         */
        Schema::create('receipt_account', function (Blueprint $table) {
            $table->id();
            $table->string('account', 20)->comment('收款账号');
            $table->unsignedInteger('company_id')->default(0)->comment('商户id');
            $table->tinyInteger('pay_type')->default(0)->comment('账户类型');
            $table->tinyInteger('active')->default(0)->comment('是否为默认收款账户0否1是');
            $table->string('username', 20)->comment('账户持有者姓名');
            $table->string('bank_name',30)->nullable()->comment('开户支行');
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
        Schema::dropIfExists('receipt_account');
    }
}
