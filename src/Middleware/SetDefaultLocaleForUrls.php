<?php

use Closure;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultLocaleForUrls
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
        $requestedtLocale = $this->localeService->getFromRequest($request, Config::get('locale.fallback_locale'));
        URL::defaults(['locale' => $requestedtLocale]);
        return $next($request);
    }
}
