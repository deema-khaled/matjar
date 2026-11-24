<?php

namespace App\Http\Middleware;

use App\Services\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'store' => config('store.name'),
            'locale' => $locale,
            'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
            // The storefront dictionary (~90 strings) for t() in Vue.
            'translations' => fn () => trans('store'),
            'cartCount' => fn () => app(Cart::class)->count(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
