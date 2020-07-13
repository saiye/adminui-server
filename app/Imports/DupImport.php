<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\GameBoard;


class DupImport implements ToCollection
{

    public function collection(Collection $rows)
    {
       $all= $rows->toArray();
       unset($all[0]);
        Validator::make($all, [
            '*.0' => 'required|numeric',
            '*.1' => 'required|max:30',
        ])->validate();
        foreach ($rows as $row) {
            if(is_numeric($row[0]) and $row[1]){
                $has=GameBoard::whereDupId($row[0])->first();
                if(!$has){
                    GameBoard::create([
                        'dup_id' => $row[0],
                        'board_name' => $row[1],
                    ]);
                }else{
                    $has->board_name=$row[1];
                    $has->save();
                }
            }
        }
    }
}
