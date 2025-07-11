# Laravel Locale

Elegant URL-Based Localization for Laravel Applications

**Laravel Locale** is a simple yet powerful package for handling localization through URL segments in Laravel. It detects, validates, and manages locale slugs (e.g., `/en`, `/tr`) in the request path, with seamless support for session storage, normalization, and middleware-based behavior.

---

## 📚 Table of Contents

- [🚀 Installation](#-installation)
- [⚙️ Configuration](#-configuration)
- [🧩 Usage](#-usage)
- [📌 Alias Usage](#-alias-usage)
- [🚦 Middleware Setup](#-middleware-setup)
- [🧠 Locale Detection Order](#-locale-detection-order)
- [⚠️ Edge Cases](#-edge-cases)
- [✅ What This Package Does](#-what-this-package-does)
- [📁 Folder Structure](#-folder-structure)
- [✅ Requirements](#-requirements)
- [📄 License](#-license)

---

# 🚀 Installation

Install via Composer:

```bash
composer require laraveledge/laravel-locale
```

---

## ⚙️ Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-locale-config
```

This will publish `config/locale.php`, where you can specify your supported locales:

```php
return [
    'supported_locales' => ['en', 'tr', 'ur'],
];
```

---

## 🧩 Usage

Wrap your localized routes using middleware and a `{locale}` prefix:

```php
use Laraveledge\LaravelLocale\Middleware\SetLocale;
use Laraveledge\LaravelLocale\Middleware\EnsureIsLocale;
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
    Route::get('/test', fn () => 'Test Page')->name('test');

    Route::fallback(function () {
        abort(404, 'Hm, why did you land here somehow?');
    }); //Neccessary , why ? see Edge Cases below
});
```

Now visit `/`, `/about`, or `/test` to be redirected to the localized versions.

---

## 📌 Alias Usage

You can also register aliases in `bootstrap/app.php` to avoid referencing class paths directly:

```php
use Laraveledge\LaravelLocale\Middleware\SetLocale;
use Laraveledge\LaravelLocale\Middleware\EnsureIsLocale;
use Laraveledge\LaravelLocale\Middleware\SetDefaultLocaleForUrls;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'ensureIsLocale' => EnsureIsLocale::class,
        'redirectMissingLocale' => RedirectMissingLocale::class,
        'setDefaultUrls' => SetDefaultLocaleForUrls::class,
        'setLocale' => SetLocale::class,
    ]);
});
```

---

## 🚦 Middleware Setup

To avoid conflicts with Laravel’s internal URL binding, make sure to set middleware priority properly:

```php
use Illuminate\Routing\Middleware\SubstituteBindings;

->withMiddleware(function (Middleware $middleware) {
    $middleware->prependToPriorityList(
        before: SubstituteBindings::class,
        prepend: SetDefaultLocaleForUrls::class,
    );
});
```

## 🚦 Full Setup (Copy Paste) - Recommended:

```php
bootstrap/app.php:

use Illuminate\Routing\Middleware\SubstituteBindings;
use Laraveledge\LaravelLocale\Middleware\SetLocale;
use Laraveledge\LaravelLocale\Middleware\EnsureIsLocale;
use Laraveledge\LaravelLocale\Middleware\SetDefaultLocaleForUrls;

   ->withMiddleware(function (Middleware $middleware): void {
     $middleware->prependToPriorityList(
        before: SubstituteBindings::class,
        prepend: SetDefaultLocaleForUrls::class,
    );
    $middleware->alias([
            'ensureIsLocale' => EnsureIsLocale::class,
            'setDefaultUrls' => SetDefaultLocaleForUrls::class,
            'setLocale' => SetLocale::class,
    ]);
})


web.php:
        Route::group([
                'prefix' => '{locale}',
                'middleware' => ['web', 'ensureIsLocale', 'setLocale', 'setDefaultUrls'] // ensure this middleware order
        ], function () {

 Route::get('/', function () {
                    return 'home';
     });

    Route::get('/about', function () {
                    return 'products';
    });

Route::get('/test', function(){
return 'test';
})->name('test'); //ensure a route named test is present in your routes so you can check/test the Locale::debug() mnethod

   Route::fallback(function () {
        abort(404, 'Hm, why did you land here somehow?');
    }); //Neccessary , why ? see Edge Cases below

});

```

## 🧠 Locale Detection Order

The package detects locale using the following order:

1. **Session** – If a locale exists in session, it is used.
2. **URL Segment** – First segment of the path.
3. **Browser Preferred Language** – Based on `Accept-Language` header.
4. **Fallback Locale** – As defined in `app.fallback_locale`.

---

## ⚠️ Edge Cases

- If your route is deeply nested like /products/1 and the locale is missing, Laravel may return a 404 before your middleware is ever triggered. This happens because Laravel tries to match the route as-is (e.g., /products/1), but it doesn't exist without a {locale} prefix, so it aborts with a 404 before even hitting the middleware stack.
- However, if you define a Route::fallback() inside your localized group, Laravel is then forced to execute the fallback when no route matches. This allows the middleware to still kick in and redirect appropriately.
- Without a fallback, shallow routes like /products will still work because Laravel can find a matching route and then the middleware handles it. But deep, parameterized routes like /products/1 will fail silently unless the fallback is present.

✅ Solution:
Always define a Route::fallback() within your {locale} route group to ensure nested and parameterized routes also get redirected when the locale is missing.

```php

Route::fallback(function () {
    abort(404, 'Hm, why did you land here somehow?');
});

---
```

## ✅ What This Package Does

- ✅ Detects and validates the locale segment in the URL
- 🔠 Normalizes casing (e.g., `EN-us` → `en`)
- 💾 Stores the selected locale in session
- 🌍 Calls `App::setLocale(...)`
- 🔁 Redirects to localized URLs if missing or invalid

---

## 🙌 Inspired by the Greats — Built with Simplicity

This package is inspired by:

- [`mcamara/laravel-localization`](https://github.com/mcamara/laravel-localization)
- [`codezero/laravel-localized-routes`](https://github.com/codezero-be/laravel-localized-routes)

These packages have served the Laravel ecosystem well and offer a wide range of features. However, as Laravel modernized its routing and middleware pipeline, we felt the need for a **simpler, lighter**, and **middleware-first** approach — without route macros or runtime route manipulation.

---

## 🎯 Why This Package Exists

In real-world projects:

- `mcamara` dynamically registers routes at runtime, which can introduce unpredictable behavior and confusion.
- `codezero` uses route macros and abstractions which deviate from native Laravel patterns.

This package solves that by offering:

✅ Clean, Laravel-native routing  
✅ No macros — just plain `Route::get()` and `Route::group()`  
✅ Middleware-first lifecycle control  
✅ SEO-optimized URL redirection and normalization  
✅ Works great with Livewire, Inertia, Blade, APIs, SPAs  
✅ Debug-friendly (`Locale::debug()`)  
✅ Lightweight, transparent, and easy to extend

---

## 📦 What This Package Is (and Isn't)

This package **intentionally starts simple**.

It currently **does not offer** some of the advanced features available in `mcamara` or `codezero` such as:

- Automatic route translation
- Locale-prefixed route generation
- URL version negotiation, etc.

We plan to add more features in future versions — while staying true to the Laravel philosophy of **clarity over magic**.

---

## 🧠 Choose Based on Your Needs

|-----------------------------------|----------------------------------------|
| You Should Use This Package If...|
|-----------------------------------|----------------------------------------|
| You want a lightweight, zero-config localization system
| You prefer Laravel-native routes with no macros
| You value predictable middleware-based flow
| You’re building something new and modern
|-----------------------------------|----------------------------------------|
| You Might Prefer Other Packages If... |
|-----------------------------------|----------------------------------------|
| You need automatic route translation
| You don’t mind learning route macros
| You need deeply integrated i18n features
| You’re maintaining legacy systems

---

## 🚀 Just Use It — You’ll Feel the Difference

Install it, add middleware, define routes — and you’re done.

```php
Route::group([
    'prefix' => '{locale}',
    'middleware' => ['web', 'ensureIsLocale', 'setLocale', 'setDefaultUrls']
], function () {
    Route::get('/', fn () => view('home'))->name('home');
});

```

## 📁 Folder Structure

```
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
```

---

## ✅ Requirements

- PHP 8.1+
- Laravel 10+

---

## 📄 License

This package is open-source and released under the MIT License.
