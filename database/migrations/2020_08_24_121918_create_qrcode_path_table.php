<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQrcodePathTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qr_code_path', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('device_id')->comment('device_id');
            $table->bigInteger('channel_id')->comment('channel_id');
            $table->bigInteger('width')->comment('width');
            $table->string('path',100)->comment('qrCode图片地址');
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
        Schema::dropIfExists('qr_code_path');
    }
}
