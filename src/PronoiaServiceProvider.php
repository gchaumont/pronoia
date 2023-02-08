<?php

namespace Pronoia;

use Illuminate\Support\ServiceProvider;

/**
 *  Elasticsearch ServiceProvider.
 */
class PronoiaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerCommands();

        $this->publishes([
            __DIR__.'/../config/package.php' => config_path('package.php'),
        ], 'pronoia-resources');
    }

    public function register()
    {
        // Add Eloquent Database driver.
        // $this->app->resolving('db', function ($db) {
        //     $db->extend('elastic', function ($config, $name) {
        //         $config['name'] = $name;

        //         return new Connection($config);
        //     });
        // });
    }

    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Console\CreateElasticsearchNode::class,
            ]);
        }
    }
}
