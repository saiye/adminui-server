<?php

namespace App\Http\Controllers\Cp\Game;

use App\Constants\PaginateSet;
use  App\Http\Controllers\Cp\BaseController as Controller;
use App\Models\GameBoard;
use Illuminate\Validation\Rule;
use Validator;

/**
 *
 * @author chenyuansai
 * @email 714433615@qq.com
 */
class BoardController extends Controller
{

    public function boardList()
    {
        $data = new GameBoard();

        if ($this->req->board_name) {
            $data = $data->where('board_name', 'like', '%' . $this->req->board_name . '%');
        }

        if ($this->req->dup_id) {
            $data = $data->whereDupId($this->req->dup_id);
        }
        $data = $data->orderBy('board_id', 'desc')->paginate($this->req->input('limit', PaginateSet::LIMIT))->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->successJson($assign);
    }


    public function addBoard()
    {
        $validator = Validator::make($this->req->all(), [
            'dup_id' => ['numeric', 'min:1', 'unique:game_board,dup_id'],
            'board_name' => 'required|max:30',
        ], [
            'board_name.required' => '板子名称，不能为空！',
            'board_name.max' => '板子名称，最长30个字符！',
            'dup_id.numeric' => '板子id,必须是一个数字！',
            'dup_id.min' => '板子id,最小值1！',
            'dup_id.unique' => '板子id,已存在！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $data = $this->req->only('dup_id', 'board_name');
        $board = GameBoard::create($data);
        if ($board) {
            return $this->successJson([], '添加成功');
        } else {
            return $this->errorJson('入库失败');
        }

    }

    public function editBoard()
    {
        $validator = Validator::make($this->req->all(), [
            'board_id' => ['numeric', 'required'],
            'dup_id' => ['required', 'numeric'],
            'board_name' => 'required|max:30',
        ], [
            'board_name.required' => '板子名称，不能为空！',
            'board_name.max' => '板子名称，最长30个字符！',
            'dup_id.required' => '板子id,必须存在！',
            'dup_id.numeric' => '板子id,必须是一个数字！',
            'dup_id.min' => '板子id,最小值1！',
            'dup_id.unique' => '板子id,必须唯一！',
        ]);
        if ($validator->fails()) {
            //返回默认支付
            return $this->errorJson('参数错误', 2, $validator->errors()->toArray());
        }
        $hasBoard = GameBoard::where('board_id', '!=', $this->req->board_id)->whereDupId($this->req->dup_id)->first();
        if ($hasBoard) {
            return $this->errorJson($hasBoard->board_name . '板子,已经使用，该dup id,不能重复入库!');
        }
        $data = $this->req->only('dup_id', 'board_name');
        $board = GameBoard::whereBoardId($this->req->board_id)->update($data);
        if ($board) {
            return $this->successJson([], '修改成功');
        } else {
            return $this->errorJson('入库失败');
        }
    }

    /**
     * 从excel导入,板子
     */
    public function excel()
    {

    }









}

