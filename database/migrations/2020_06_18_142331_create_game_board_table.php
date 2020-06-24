<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameBoardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_board', function (Blueprint $table) {
            $table->increments('board_id')->comment('自增id');
            $table->string('board_name',30)->comment('板子名称');
            $table->integer('dup_id')->unique()->comment('板子id');
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
        Schema::dropIfExists('game_board');
    }
}
