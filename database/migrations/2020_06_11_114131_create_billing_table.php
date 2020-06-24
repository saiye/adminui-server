<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing', function (Blueprint $table) {
            $table->increments('billing_id')->comment('计费模式id');
            $table->string('billing_name',15)->comment('模式名称');
            $table->decimal('price',8,2)->comment('计费单价');
            $table->tinyInteger('price_type')->comment('货币类型');
            $table->tinyInteger('time_unit')->comment('计费单位');
            $table->tinyInteger('time_type')->comment('计费时间类型');
            $table->unsignedInteger('store_id')->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
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
        Schema::dropIfExists('billing');
    }
}
