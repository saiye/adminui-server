<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_tag', function (Blueprint $table) {
            $table->increments('tag_id')->comment('tag_id');
            $table->string('tag_name',50)->comment('标签名称');
            $table->unsignedInteger('store_id')->default(0)->comment('所属店面');
            $table->unsignedInteger('company_id')->default(0)->comment('所属公司id');
            $table->unsignedInteger('category_id')->default(0)->comment('所属分类id');
            $table->tinyInteger('is_del')->default(0)->comment('是否删除');
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
        Schema::dropIfExists('goods_tag');
    }
}
