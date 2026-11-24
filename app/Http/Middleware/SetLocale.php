<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale comes from the session (set by the language switcher); first-time
 * visitors get Arabic if their browser prefers it.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('store.locales');

        // Shareable links: /shop?lang=ar opens the Arabic store and remembers it.
        if (in_array($request->query('lang'), $supported, true)) {
            $request->session()->put('locale', $request->query('lang'));
        }

        $locale = $request->session()->get('locale')
            ?? $request->getPreferredLanguage($supported)
            ?? config('app.locale');

        app()->setLocale(in_array($locale, $supported, true) ? $locale : 'en');

        return $next($request);
    }
}
