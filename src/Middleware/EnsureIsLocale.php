<?php 
namespace Laraveledge\LaravelLocale\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laraveledge\Services\LocaleService;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function __construct(
        protected LocaleService $locale
    ){}

    public function handle(Request $request, Closure $next): Response
    {

        $segment = $request->segment(1);

        if(!$request->segments()){
            $this->locale->debug($request);
            return $this->locale->redirectToLocalizedRoot($request);
        }

        if(!$this->locale->looksLikeLocale($segment)){
            return $this->locale->redirectWithLocale($request);
        }


        return $next($request);
    }

}
