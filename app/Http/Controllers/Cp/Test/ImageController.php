<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Http\Controllers\Cp\Test;

use App\Http\Controllers\Cp\BaseController;
use App\Models\Image;
use Redirect;
use Auth;
use Route;

class ImageController extends BaseController
{
    public function getList()
    {
        $data = new Image();
        if ($this->req->image_name) {
            $data = $data->where('image_name', 'like', '%' . $this->req->image_name . '%');
        }
        if ($this->req->type) {
            $data = $data->whereType($this->req->type);
        }
        $data = $data->orderBy('id', 'desc')->paginate(30)->appends($this->req->except('page'));
        $assign = compact('data');
        return $this->view('test.image.list', $assign);
    }

    public function add()
    {
        $this->validate($this->req, [
            'avatar' => 'required|image',
            'reduce' => 'required|integer|min:5',
            'image_name' => 'required|max:10',
        ]);
        $reduce = $this->req->post('reduce');
        $path = $this->req->file('avatar')->store('avatars');

        $obj = new \App\Lib\ImageCut([
            'file' => storage_path('app' . DIRECTORY_SEPARATOR . 'public') . DIRECTORY_SEPARATOR . $path,
            'width' => $reduce,
            'height' => $reduce,
            'type' => 2,
            'save_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'cut'),
        ]);

        $cut_path = $obj->save();
        $info = $obj->info();

        $data = [
            'image_name' => $this->req->post('image_name'),
            'type' => $info[2],
            'reduce' => $reduce,
            'image_path' => $path,
            'image_compress_path' => '/cut/' . $cut_path,
        ];
        Image::insert($data);


        return Redirect::to(route('cp-imageList'));
    }

    public function index()
    {
       // $user = Auth::guard('cp')->login(1)->user();
        //Auth::login($user);
      //  $menus = $user->roleMenu();
       // $act = explode('/', request()->path());
       // $assign = compact('menus', 'act');
        return $this->view('home');
    }


}
