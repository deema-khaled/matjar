<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __('status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'info',
            self::Shipped => 'primary',
            self::Delivered => 'success',
            self::Cancelled => 'gray',
        };
    }

    /** Orders that still count towards revenue. */
    public function countsAsSale(): bool
    {
        return $this !== self::Cancelled;
    }

    /** Steps shown on the tracking page, in order. */
    public static function timeline(): array
    {
        return [self::Pending, self::Processing, self::Shipped, self::Delivered];
    }
}
