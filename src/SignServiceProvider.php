<?php

namespace Yjtec\Sign;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Yjtec\Sign\SignGuard;
class SignServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::extend('sign',function($app,$name,array $config){
            return new SignGuard(
                Auth::createUserProvider($config['provider']),
                $this->app['request']
            );
        });
    }
}
