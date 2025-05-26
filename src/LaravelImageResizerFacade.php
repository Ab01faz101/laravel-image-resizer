<?php

namespace Ab01faz101\TailAlert;

use Illuminate\Support\Facades\Facade;

class TailAlertFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'laravel_image_resizer';
    }

}
