<?php

namespace App\Providers;

use App\Service\Pay\WeiXinPayApi;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Service\Order\CheckGoodsOrder;
use App\Service\Order\CheckRoomOrder;
use App\Service\Order\DefaultCheckOrder;
use App\Service\Pay\DefaultPayApi;
use App\Service\LoginApi\WeiXinAppLoginApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Service\LoginApi\LoginApi',
            'App\Service\LoginApi\WeiXinLoginApi'
        );
        $this->app->bind(
            'App\Service\GameApi\GameApi',
            'App\Service\GameApi\LrsApi'
        );
        //微信app登录api
        $this->app->bind('WeiXinAppLoginApi', function ($app) {
            return new WeiXinAppLoginApi($app->make('request'));
        });
        $this->app->bind('CheckGoodsOrder', function ($app) {
            return new CheckGoodsOrder($app);
        });
        $this->app->bind('CheckRoomOrder', function ($app) {
            return new CheckRoomOrder($app);
        });
        $this->app->bind('DefaultCheckOrder', function ($app) {
            return new DefaultCheckOrder($app);
        });
        $this->app->bind('WeiXinPayApi', function ($app) {
            return new WeiXinPayApi($app->make('request'));
        });
        $this->app->bind('DefaultPayApi', function ($app) {
            return new DefaultPayApi($app->make('request'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //en ,zh-cn
        $locale=$this->app->make('request')->header('locale','zh_cn');
        $this->app->setLocale($locale);
        Schema::defaultStringLength(191);
        $isDebug = Config::get('app.debug');
        if ($isDebug) {
            DB::listen(
                function ($sql) {
                    foreach ($sql->bindings as $i => $binding) {
                        if ($binding instanceof \DateTime) {
                            $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                        } else {
                            if (is_string($binding)) {
                                $sql->bindings[$i] = "'$binding'";
                            }
                        }
                    }
                    // Insert bindings into query
                    $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);

                    $query = vsprintf($query, $sql->bindings);

                    // Save the query to file
                    $logFile = fopen(
                        storage_path('logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_query.log'),
                        'a+'
                    );
                    fwrite($logFile, date('Y-m-d H:i:s') . ': ' . $query . PHP_EOL);
                    fclose($logFile);
                }
            );
        }
    }
}
