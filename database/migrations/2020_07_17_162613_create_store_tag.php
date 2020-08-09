<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tag_id')->default(0)->comment('tag_id');
            $table->string('tag_name', 30)->comment('商店标签');
            $table->unsignedInteger('store_id')->default(0)->comment('店铺id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_tag');
    }
}
