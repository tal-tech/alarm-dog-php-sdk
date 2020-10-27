<?php

declare(strict_types=1);

namespace Dog\Alarm\Provider;

use Dog\Alarm\Alarm;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->publishConfig();

        $this->register();
    }

    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(Alarm::class, function ($app) {
            return new Alarm($app);
        });
    }

    /**
     * 发布配置文件.
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../../config/dog.php' => config_path('dog.php'),
        ]);
    }
}
