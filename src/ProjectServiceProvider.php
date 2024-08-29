<?php

namespace Bertozzi\Project;

use Bertozzi\Project\Facades\ProjectManager;
use Illuminate\Support\ServiceProvider;

class ProjectServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/project.php', 'project');

        $this->app->singleton('project', function ($app) {
            return new ProjectManager();
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'project');

        $this->publishes([
            __DIR__.'/../config/project.php' => config_path('project.php'),
        ], 'config');
    }
}
