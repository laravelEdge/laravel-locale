<?php

use Illuminate\Support\Facades\Facade;

class Locale extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LocaleService::class;
    }
}
