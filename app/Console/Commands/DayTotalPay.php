<?php

namespace App\Console\Commands;

use App\Models\WithdrawLog;
use Illuminate\Console\Command;

class DayTotalPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dayTotalPay:{date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每个店铺天流水统计!';

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

        $date=$this->argument('date')?$this->argument('date'):((date('G')<1)?date('Y-m-d',strtotime('-1day')):date('Y-m-d'));
        $log=WithdrawLog::whereDate($date)->first();
    }
}
