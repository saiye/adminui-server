<?php

namespace App\Jobs;

use App\Models\PlayerGameLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RoomGameLog;
use App\Models\PlayerCountRecord;

class RoomGameLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大失败次数
     * @var int
     */
    public $tries = 5;
    /**
     * @var int
     * 最大异常数
     */
    public $maxExceptions = 3;

    /**
     *该任务允许运行的最大时长
     */
    public $timeout = 10;


    public $post = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 插入对局数据
        $gameLog = RoomGameLog::create([
            'gameRes' => $this->post['gameRes'],
            'replayContentJson' => $this->post['replayContentJson']??'{}',
        ]);
        $playerlogs = [];
        $playercounts = [];
        foreach ($this->post['unitInfos'] as $unit) {
            // 跟新统计数据
            $record = PlayerCountRecord::find($unit["userId"]);
            if (!$record) {
                $countdata['user_id'] = $unit["userId"];
                $countdata['total_game'] = 1;
                $countdata['win_game'] = $unit["res"] == 2 ? 1 : 0;
                $countdata['mvp'] = $unit["mvp"] != 0 ? 1 : 0;
                $countdata['svp'] = $unit["svp"] != 0 ? 1 : 0;
                $countdata['police'] = $unit["police"] != 0 ? 1 : 0;
                array_push($playercounts, $countdata);
            } else {
                if($unit['res']!==1){
                    $record->total_game += 1;
                    $record->win_game += $unit["res"] == 2 ? 1 : 0;
                    $record->mvp += $unit["mvp"] != 0 ? 1 : 0;
                    $record->svp += $unit["svp"] != 0 ? 1 : 0;
                    $record->police += $unit["police"] != 0 ? 1 : 0;
                    $record->save();
                }
            }
            $playerdata['user_id'] = $unit["userId"];
            $playerdata['dup_id'] = $this->post['dupId'];
            $playerdata['seat'] = $unit["seat"];
            $playerdata['job'] = $unit["job"];
            $playerdata['res'] = $unit["res"];
            $playerdata['begin_tick'] = $this->post['beginTick'];
            $playerdata['score'] = $unit["score"];
            $playerdata['mvp'] = $unit["mvp"];;
            $playerdata['svp'] =$unit["svp"];
            $playerdata['status'] = $unit["status"];
            $playerdata['room_game_id'] = $gameLog->id;
            array_push($playerlogs, $playerdata);
        }
        // 插入记录
        if (count($playercounts) > 0) {
            PlayerCountRecord::insert($playercounts);
        }
        PlayerGameLog::insert($playerlogs);
    }
}
