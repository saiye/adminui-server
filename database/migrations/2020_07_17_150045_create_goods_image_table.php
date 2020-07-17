<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_image', function (Blueprint $table) {
            $table->increments('goods_image_id')->comment('goods_image_id');
            $table->string('image',100)->comment('图片地址');
            $table->unsignedInteger('goods_id')->default(0)->comment('商品id');
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
        Schema::dropIfExists('goods_image');
    }
}
