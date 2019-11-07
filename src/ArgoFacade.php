<?php

namespace Theomessin\Argo;

use Illuminate\Support\Facades\Facade as Facade;

class ArgoFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'argo';
    }
}
