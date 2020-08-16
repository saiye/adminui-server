<?php

namespace App\Console\Commands;

use App\Models\Order;
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
        $date = $this->argument('date') ? $this->argument('date') : ((date('G') < 1) ? date('Y-m-d', strtotime('-1day')) : date('Y-m-d'));
        $nowDate = date('Y-m-d H:i:s');
        Order::select('store_id', 'company_id', DB::raw('sum("actual_payment") as price'))->wherePayStatus(1)->whereBetween('created_at', [$date . '00:00:00', $date . '23:59:59'])->groupBy('store_id')->groupBy('company_id')->chunk(10, function ($res) use ($date, $nowDate) {
            $data = [];
            foreach ($res as $item) {
                $hasLog = WithdrawLog::whereStoreId($item->store_id)->whereDate($date)->first();
                if ($hasLog) {
                    $hasLog->fill([
                        'store_id' => $item->store_id,
                        'company_id' => $item->company_id,
                        'total_price' => $item->price,
                        'date' => $date,
                    ]);
                    $hasLog->save();
                } else {
                    array_push($data, [
                        'store_id' => $item->store_id,
                        'company_id' => $item->company_id,
                        'total_price' => $item->price,
                        'date' => $date,
                        'created_at' => $nowDate,
                        'updated_at' => $nowDate,
                    ]);
                }
            }
            if ($data)
                WithdrawLog::insert($data);
        });
    }
}
