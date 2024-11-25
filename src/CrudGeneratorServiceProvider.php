<?php

namespace CrudGenerator;

use Illuminate\Support\ServiceProvider;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Correct route path
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    
        // Load views
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'crud-generator');
    
        // Publish assets
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/crud-generator'),
        ], 'crud-generator-assets');
    }
    

    /**
     * Register services.
     */
    public function register()
    {
        // Register package commands
        // $this->commands([
        //     \CrudGenerator\Commands\GenerateCrudCommand::class,
        // ]);
    }
}
