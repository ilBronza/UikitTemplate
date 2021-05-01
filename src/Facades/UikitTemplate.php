<?php

namespace IlBronza\UikitTemplate\Facades;

use Illuminate\Support\Facades\Facade;

class UikitTemplate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'uikittemplate';
    }
}
