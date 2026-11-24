<?php

namespace App\Models\Concerns;

/**
 * Columns stored per language (name_en / name_ar) and read in the current locale:
 * $product->translated('name') returns name_ar when the site is in Arabic,
 * falling back to English when the Arabic text is empty.
 */
trait HasTranslatedColumns
{
    public function translated(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $this->getAttribute("{$field}_{$locale}") ?: $this->getAttribute("{$field}_en");
    }
}
