<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountryZoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country_zone', function (Blueprint $table) {
            $table->id();
            $table->string('name_zh_cn',32)->comment('国家名称');
            $table->string('name_en',32)->comment('国家英文名称');
            $table->string('name_zh_hk',32)->comment('国家繁体名称');
            $table->integer('area_code')->comment('区号');
            $table->string('letter_en',2)->comment('英文名称首字母');
            $table->string('letter_zh_cn',2)->comment('中文名称首字母');
            $table->string('pattern',64)->default('/^[0-9]*$/')->comment('号码正则');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country_zone');
    }
}
