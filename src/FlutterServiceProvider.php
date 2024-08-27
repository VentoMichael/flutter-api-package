<?php

namespace Vendor\FlutterPackage;

use Illuminate\Support\ServiceProvider;

class FlutterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            \Vendor\FlutterPackage\Console\Commands\MakeFlutterCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
