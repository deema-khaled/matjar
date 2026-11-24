<?php

namespace App\Support;

/**
 * Prices are stored as integers in the smallest currency unit
 * (e.g. 1 JOD = 1000 fils), so no floating-point rounding errors.
 */
final class Money
{
    public static function format(int $amount, ?string $locale = null): string
    {
        $currency = config('store.currency');
        $value = number_format($amount / $currency['subunits'], $currency['decimals']);
        $symbol = ($locale ?? app()->getLocale()) === 'ar' ? $currency['symbol_ar'] : $currency['symbol_en'];

        return ($locale ?? app()->getLocale()) === 'ar' ? "{$value} {$symbol}" : "{$symbol} {$value}";
    }

    public static function toMajor(int $amount): float
    {
        return $amount / config('store.currency.subunits');
    }

    public static function fromMajor(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * config('store.currency.subunits'));
    }
}
