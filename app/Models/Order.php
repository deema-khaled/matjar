<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'number', 'status', 'customer_name', 'phone', 'email', 'city', 'address', 'notes',
        'payment_method', 'subtotal', 'shipping', 'total', 'locale',
    ];

    protected $attributes = [
        'status' => 'pending',
        'payment_method' => 'cod',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'integer',
            'shipping' => 'integer',
            'total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Human-friendly, non-sequential-looking number: MTJ-24A7Q9.
        static::creating(function (Order $order) {
            $order->number ??= static::newNumber();
        });
    }

    public static function newNumber(): string
    {
        // No 0/O or 1/I, so customers can read it over the phone.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $number = 'MTJ-';
            for ($i = 0; $i < 6; $i++) {
                $number .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::where('number', $number)->exists());

        return $number;
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @param  Builder<Order>  $query */
    public function scopeSales(Builder $query): void
    {
        $query->where('status', '!=', OrderStatus::Cancelled->value);
    }

    /** Cancels the order and puts its items back into stock (once). */
    public function cancel(): void
    {
        if ($this->status === OrderStatus::Cancelled) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->items()->with('product')->get() as $item) {
                $item->product?->increment('stock', $item->quantity);
            }

            $this->update(['status' => OrderStatus::Cancelled]);
        });
    }

    /** Phone numbers are compared by digits only, so "+962 79-123" matches "96279123". */
    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }

    public function phoneMatches(string $phone): bool
    {
        return static::normalizePhone($phone) !== '' && static::normalizePhone($phone) === static::normalizePhone($this->phone);
    }
}
