<?php
namespace Ab01faz101\TailAlert;
use Illuminate\Support\ServiceProvider;
use Ab01faz101\LaravelImageResizer\LaravelImageResizer;

class TailAlertServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton('tail_alert', function () {
            return new LaravelImageResizer();
        });

        $this->mergeConfigFrom(__DIR__ . '/Config/size.php' , 'laravel-image-resizer');
    }


    public function boot()
    {

    }

}
