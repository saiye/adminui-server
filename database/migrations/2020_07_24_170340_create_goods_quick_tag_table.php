<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsQuickTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_quick_tag', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name', 30)->comment('快速标签名');
            $table->unsignedInteger('store_id')->default(0)->comment('所属店面');
            $table->text('config')->comment('快速标签配置json');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_quick_tag');
    }
}
