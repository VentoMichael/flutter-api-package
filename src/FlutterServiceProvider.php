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
        // Register the command
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
        // You can publish assets, config files, etc. here if needed
    }
}
