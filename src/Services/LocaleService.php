<?php 

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;




class LocaleService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function looksLikeLocale(string $segment): bool
    {

        return preg_match('/^[a-z]{2}([-_][a-z]{2})?$/i', $segment);
    }

    public function redirectWithLocale(Request $request, int $code = 301, $locale = null): RedirectResponse
    {
        $queryString = $request->getQueryString();
        $formattedQueryString = $queryString ? '?' . $queryString : '';
        $segments = $request->segments();
        if (!$locale) {
            $locale = $this->getLocale($request);
        }
        array_unshift($segments, $locale);
        $segmentedUrl = implode('/', array_map('rawurlencode', $segments));
        return Redirect::to('/' . $segmentedUrl . $formattedQueryString, $code);
    }

    public function insertAndRedirect(Request $request, int $code = 301, $locale = null)
    {
        $queryString = $request->getQueryString();
        $formattedQueryString = $queryString ? '?' . $queryString : '';
        $segments = $request->segments();
        if (!$locale) {
            $locale = $this->getLocale($request);
        }
        $segments[0] = $locale;
        $segmentedUrl = implode('/', array_map('rawurlencode', $segments));
        return Redirect::to('/' . $segmentedUrl . $formattedQueryString, $code);
    }

    public function getLocale(Request $request): string
    {

        $isPreferred = $this->preferred($request);
        $isDefault = $this->default();
        $isFromSession = $this->getFromSession($request);

        return $isFromSession ?? $isPreferred ?? $isDefault;
    }

    public function getFromRequest(Request $request, $default = null)
    {
        $segments = $request->segments();
        $localeSegment = $segments[0] ?? null;
        if (!$localeSegment) {
            return null;
        }

        if (!$this->looksLikeLocale($localeSegment)) {
            return $default ?? null;
        }

        return $localeSegment ?? $default;
    }

    public function preferred(Request $request)
    {

        $supported = Config::get('locale.supported_locales', ['en']);
        $preferred = $request->getPreferredLanguage($supported);
        return $preferred;
    }

    public function getFromSession($request)
    {
        $supported = Config::get('locale.supported_locales', ['en']);
        if (!Session::get('locale') || !in_array(Session::get('locale'), $supported)) {
            return null;
        }
        return Session::get('locale');
    }

    public function default()
    {
        return Config::get('app.fallback_locale', 'en');
    }

    public function redirectToLocalizedRoot(Request $request)
    {
        $locale = $this->getLocale($request);
        return Redirect::to("/$locale", 301);
    }

    public function getSupported(): array|null
    {
        return Config::get('locale.supported_locales', ['en']);
    }

    public function isSupported($locale)
    {
        return in_array($locale, $this->getSupported(), true);
    }

    public function containsRegion($locale)
    {
        return Str::contains($locale, ['-', '_']);
    }

    public function debug($request)
    {
        //change this to getlocale to get from session first
        $preferredLocale = $this->preferred($request);

        // Get the first segment of url, e.g, en => /en/home
        $firstSegment = $request->segment(1);

        // Split locale like 'en-US', 'EN_us' or 'en_us' to get 'en' and convert to lowercase if uppercase
        $originalLocale = Str::of($firstSegment)->before('-')->before('_')->value();
        $normalizedLocale = Str::lower($originalLocale);

        return dd([
            'original_url' => $request->url(),
            'path_info' => $request->getPathInfo(),
            'segment' => $firstSegment,
            'origanl_locale' => $originalLocale,
            'normalized' => $normalizedLocale,
            'supported_locales' => $this->getSupported(),
            'locale_is_supported' => $this->isSupported($normalizedLocale),
            'malformed_or_upper_case' => $originalLocale !== $normalizedLocale,
            'contains_region' => $this->containsRegion($firstSegment),
            'session_locale' => $this->getFromSession($request),
            'browser_preferred_locale' => $this->preferred($request),
            'default_locale' => $this->default(),
            'test_route' => Url::route('test') // create a test route to see this
        ]);
    }

    public function set(string $locale)
    {
        if ($this->isSupported($locale)) {
            if ($locale !== Session::get('locale')) {
                Session::put('locale',  $locale);
            }
            App::setLocale($locale);
        }
    }

    public function unset()
    {
        Session::forget('locale');
        App::setLocale($this->default());
    }
}
