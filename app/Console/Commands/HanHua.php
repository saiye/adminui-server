<?php

namespace App\Console\Commands;

use App\Lib\ZhConvert;
use App\Models\CountryZone;
use Illuminate\Console\Command;

class HanHua extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'han';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '国家地区，简体转繁体,并入库!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      CountryZone::chunk(5,function ($list){
            foreach ($list as $item){
               $item->name_zh_hk=ZhConvert::zh($item->name_zh_cn);
                $item->save();
            }
        });

    }
}
