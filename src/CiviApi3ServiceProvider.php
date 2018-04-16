<?php

namespace Leanwebstart\CiviApi3;

use Illuminate\Support\ServiceProvider;

class CiviApi3ServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //Config
        $source = realpath($raw = __DIR__.'/../config/civi-api3.php') ?: $raw;
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('civi-api3.php')]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}