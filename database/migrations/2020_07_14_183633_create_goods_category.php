<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_category', function (Blueprint $table) {
            $table->increments('category_id')->comment('category_id');
            $table->string('category_name',50)->default(0)->comment('分类名称');
            $table->unsignedInteger('store_id')->default(0)->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
            $table->unsignedInteger('count')->default(0)->comment('商品数量');
            $table->tinyInteger('is_del')->default(0)->comment('是否删除!');
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
        Schema::dropIfExists('goods_category');
    }
}
