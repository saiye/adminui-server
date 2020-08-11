<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/8/10
 * Time: 10:24
 */

namespace App\Service\Order;


use \Illuminate\Contracts\Foundation\Application;

abstract class CheckBase implements CheckOrder
{
    protected $validationFactory = null;
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->validationFactory = $app->make('validator');
    }
}
