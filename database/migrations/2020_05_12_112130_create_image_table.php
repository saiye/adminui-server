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
            $table->string('image_name',10)->comment('图片名称');
            $table->tinyInteger('type')->default(1)->comment('图片类型1png,2jpg');
            $table->integer('reduce')->default(100)->comment('压缩大小1-100');
            $table->string('image_path')->comment('图片源图路径');
            $table->string('image_compress_path')->comment('压缩图片路径');
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
