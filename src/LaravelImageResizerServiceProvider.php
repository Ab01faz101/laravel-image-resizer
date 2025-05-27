<?php
namespace Ab01faz101\LaravelImageResizer;
use Illuminate\Support\ServiceProvider;

class LaravelImageResizerServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton('laravel_image_resizer', function () {
            return new LaravelImageResizer();
        });

        $this->mergeConfigFrom(__DIR__ . '/Configs/laravel_image_resizer.php', 'laravel_image_resizer');
    }


    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Configs/laravel_image_resizer.php' => config_path('laravel_image_resizer.php'),
        ], 'config');
    }

}
