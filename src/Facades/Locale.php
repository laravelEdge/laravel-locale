<?php

use Illuminate\Support\Facades\Facade;
use Laraveledge\Services\LocaleService;

class Locale extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LocaleService::class;
    }
}
