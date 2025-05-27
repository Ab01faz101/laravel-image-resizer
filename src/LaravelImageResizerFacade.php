<?php

namespace Ab01faz101\LaravelImageResizer;

use Illuminate\Support\Facades\Facade;

class LaravelImageResizerFacade extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return 'laravel_image_resizer';
    }

}
