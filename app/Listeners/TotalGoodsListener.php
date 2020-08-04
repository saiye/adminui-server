<?php

namespace App\Listeners;

use App\Events\TotalGoodsEvent;
use App\Models\Goods;
use App\Models\GoodsCategory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TotalGoodsListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TotalGoodsEvent  $event
     * @return void
     */
    public function handle(TotalGoodsEvent $event)
    {
        $count=Goods::whereCategoryId($event->goods->category_id)->count();

        GoodsCategory::whereCategoryId($event->goods->category_id)->update([
            'count'=>$count
        ]);
    }
}
