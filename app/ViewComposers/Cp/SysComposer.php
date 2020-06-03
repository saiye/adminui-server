<?php
namespace App\ViewComposers\Cp;
use Illuminate\Contracts\View\View;
use App\ViewComposers\BaseComposer;
use Auth;
class SysComposer extends BaseComposer
{

    /**
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * 绑定数据到视图.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $act =request()->path();
        $user=Auth::guard('cp')->user();
        $menus =$user->roleMenu();
        $view->with('menus', $menus);
        $view->with('act', explode('/',$act));
    }
}
