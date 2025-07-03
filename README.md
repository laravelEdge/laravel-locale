# Laravel Locale

Elegant URL-Based Localization for Laravel Applications

**Laravel Locale** is a simple yet powerful package for handling localization through URL segments in Laravel. It detects, validates, and manages locale slugs (e.g., `/en`, `/tr`) in the request path, with seamless support for session storage, normalization, and middleware-based behavior.

---

## 🚀 Installation

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
```

### ✅ What This Package Does

* ✅ Detects and validates the locale segment in the URL
* 🔠 Normalizes casing (e.g., `EN-us` → `en`)
* 💾 Stores the selected locale in session
* 🌍 Calls `App::setLocale(...)`
* 🔁 Redirects to localized URLs if missing or invalid

---

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

* PHP 8.1+
* Laravel 10+

---

## 📄 License

This package is open-source and released under the MIT License.
