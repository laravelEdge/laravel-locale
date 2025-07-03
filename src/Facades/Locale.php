<?php

namespace Laraveledge\LaravelLocale\Facades;

use Illuminate\Support\Facades\Facade;
use Laraveledge\LaravelLocale\Services\LocaleService;

class Locale extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LocaleService::class;
    }
}
