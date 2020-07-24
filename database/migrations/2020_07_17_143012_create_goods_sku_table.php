<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsSkuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_sku', function (Blueprint $table) {
            $table->increments('sku_id')->comment('sku_id');
            $table->string('suk_name',50)->comment('规格名称');
            $table->decimal('goods_price',8,3)->comment('规格单价');
            $table->unsignedInteger('goods_id')->default(0)->comment('goods_id');
            $table->unsignedInteger('tag_id')->default(0)->comment('tag_id');
            $table->tinyInteger('is_del')->default(0)->comment('是否删除');
            $table->tinyInteger('is_act')->default(0)->comment('是否默认选中');
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
        Schema::dropIfExists('goods_sku');
    }
}
