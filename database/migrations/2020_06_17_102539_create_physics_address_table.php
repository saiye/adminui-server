<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhysicsAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('physics_address', function (Blueprint $table) {
            $table->id()->comment('设备短id');
            $table->string('physics_id',128)->index()->comment('设备物理地址');
        });
        DB::statement("ALTER TABLE physics_address AUTO_INCREMENT = 1000;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('physics_address');
    }
}
