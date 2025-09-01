<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has a preferred locale
        if (Auth::check() && Auth::user()->preferred_locale) {
            App::setLocale(Auth::user()->preferred_locale);
        } 
        // Check for locale in request header
        elseif ($request->hasHeader('Accept-Language')) {
            $locale = $request->getPreferredLanguage(config('app.available_locales'));
            if ($locale) {
                App::setLocale($locale);
            }
        }
        // Check for locale in query parameter
        elseif ($request->has('locale') && in_array($request->get('locale'), config('app.available_locales'))) {
            App::setLocale($request->get('locale'));
        }

        return $next($request);
    }
}
