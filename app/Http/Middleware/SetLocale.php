<?php

namespace App\Http\Middleware;

use App\Enums\Language;
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
        // Try to get locale from authenticated user first
        if (Auth::check()) {
            $userLanguage = Auth::user()->language ?? Language::default();
            App::setLocale($userLanguage);
        } else {
            // Fallback to header or default
            $locale = $request->header('Accept-Language');
            $localeSet = false;

            // Parse Accept-Language header to get preferred language
            if ($locale && trim($locale) !== '') {
                $preferredLocales = $this->parseAcceptLanguage($locale);

                foreach ($preferredLocales as $lang) {
                    if (in_array($lang, Language::getValues())) {
                        App::setLocale($lang);
                        $localeSet = true;
                        break;
                    }
                }
            }

            // If no valid locale found in header, use default
            if (! $localeSet) {
                App::setLocale(Language::default());
            }
        }

        return $next($request);
    }

    /**
     * Parse Accept-Language header
     */
    private function parseAcceptLanguage(string $acceptLanguage): array
    {
        $locales = [];

        foreach (explode(',', $acceptLanguage) as $locale) {
            $locale = trim($locale);
            if (strpos($locale, ';') !== false) {
                $locale = explode(';', $locale)[0];
            }

            // Extract language code (e.g., 'fr' from 'fr-FR')
            if (strpos($locale, '-') !== false) {
                $locale = explode('-', $locale)[0];
            }

            $locales[] = strtolower(trim($locale));
        }

        return array_unique($locales);
    }
}
