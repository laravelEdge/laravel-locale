<?php 

namespace Laraveledge\LaravelLocale\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laraveledge\Services\LocaleService;
use Symfony\Component\HttpFoundation\Response;



/**
 * Middleware to normalize and enforce supported locale routing.
 *
 * Handles malformed locales (e.g., `en-US` → `en`) and redirects accordingly.
 * Ensures that only configured languages like `en`, `tr`, `ur`, etc. are accepted.
 *
 * Developed by Shaharyar Ahmed for 
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    public function __construct(
        protected LocaleService $localeService
    ){}
    public function handle(Request $request, Closure $next): Response
    {


        //change this to getlocale to get from session first
        $preferredLocale = $this->localeService->preferred($request);

        // Get the first segment of url, e.g, en => /en/home
        $firstSegment = $request->segment(1);

        // Split locale like 'en-US', 'EN_us' or 'en_us' to get 'en' and convert to lowercase if uppercase
        $originalLocale = Str::of($firstSegment)->before('-')->before('_')->value();
        $normalizedLocale = Str::lower($originalLocale);

        // If it's not even in the supported list, redirect to preferred locale
        if (!$this->localeService->isSupported($normalizedLocale)) {
            return $this->localeService->insertAndRedirect($request, 301, $preferredLocale);
        }

        // Force redirect if original had uppercase or malformed casing
        if ($originalLocale !== $normalizedLocale) {
            return $this->localeService->insertAndRedirect($request, 301, $normalizedLocale);
        }

        if ($this->localeService->containsRegion($firstSegment)) {
            return $this->localeService->insertAndRedirect($request, 301, $normalizedLocale);
        }

        // $this->localeService->debug($request);

        // All clean – set locale
        $this->localeService->unset();
        $this->localeService->set($normalizedLocale);

        return $next($request);
    }
}


