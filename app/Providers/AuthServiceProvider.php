<?php

namespace App\Providers;

use Illuminate\Auth\TokenGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Service\Auth\JwtGuard;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    public function register()
    {
        Auth::extend('jwt', function($app, $name, array $config) {
            return new JwtGuard(Auth::createUserProvider($config['provider']),$app['request'],
                $config['input_key'] ?? 'token',
                $config['storage_key'] ?? 'token',
                $config['hash'] ?? false);
        });
    }
}
