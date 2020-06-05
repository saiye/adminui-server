<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('path')->comment('图片路径');
            $table->tinyInteger('type')->comment('图片类型,1商户营业执照,2店面照片');
            $table->tinyInteger('is_del')->default(0)->comment('删除状态');
            $table->unsignedInteger('foreign_id')->comment('外键id');
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
        Schema::dropIfExists('images');
    }

}
