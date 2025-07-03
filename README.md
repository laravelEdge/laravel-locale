Laravel Locale

A simple and robust package for URL-based locale handling in Laravel. It detects, validates, and manages locale segments (e.g., /en, /tr) in the request URL, with support for session storage, normalization, and middleware-based localization.

Installation

Install via Composer:

composer require laraveledge/laravel-locale

Configuration

Publish the configuration file:

php artisan vendor:publish --tag=laravel-locale-config

This will publish config/locale.php, where you can define supported locales:

return [
    'supported_locales' => ['en', 'tr', 'ur'],
];

Usage

Wrap your localized routes with middleware and a {locale} prefix:

use Laraveledge\LaravelLocale\Middleware\EnsureIsLocale;
use Laraveledge\LaravelLocale\Middleware\SetLocale;
use Laraveledge\LaravelLocale\Middleware\SetDefaultLocaleForUrls;

Route::group([
    'prefix' => '{locale}',
    'middleware' => [
        EnsureIsLocale::class,
        SetLocale::class,
        SetDefaultLocaleForUrls::class,
        'web',
    ],
], function () {
    Route::get('/', fn () => 'Home Page')->name('home');
    Route::get('/about', fn () => 'About Page');
});

The package will:

Detect and validate the locale segment

Normalize casing (e.g., EN-us → en)

Store the locale in session

Apply App::setLocale(...)

Redirect to localized URLs if missing or invalid

Folder Structure

src/
├── Config/
│   └── locale.php
├── Middleware/
│   ├── EnsureIsLocale.php
│   ├── SetLocale.php
│   └── SetDefaultLocaleForUrls.php
├── Services/
│   └── LocaleService.php
├── Facades/
│   └── Locale.php (optional)
└── LaravelLocaleServiceProvider.php

Requirements

PHP 8.1+

Laravel 10+

License

This package is open-source and released under the MIT License.

