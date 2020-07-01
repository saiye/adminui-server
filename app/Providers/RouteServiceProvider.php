<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapClientRoutes();

        $this->mapWxRoutes();
    }


    protected function mapApiRoutes()
    {
        //prefix('api')
        Route::middleware('cp')
            ->namespace($this->namespace . '\\Cp')
            ->group(base_path('routes/cp.php'));
    }

    protected function mapClientRoutes()
    {
        Route::prefix('client')->middleware('client')
            ->namespace($this->namespace . '\\Client')
            ->group(base_path('routes/client.php'));
    }

    protected function mapWxRoutes()
    {
        Route::prefix('wx')
            ->namespace($this->namespace . '\\Wx')
            ->group(base_path('routes/wx.php'));
    }
}
