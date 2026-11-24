<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $thisMonth = Order::sales()->where('created_at', '>=', now()->startOfMonth());
        $lastMonth = Order::sales()->whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()]);

        $revenue = (int) $thisMonth->clone()->sum('total');
        $previous = (int) $lastMonth->sum('total');
        $change = $previous > 0 ? round(($revenue - $previous) / $previous * 100) : null;
        $orders = $thisMonth->clone()->count();

        // Revenue per day for the last 14 days, for the sparkline.
        $daily = Order::sales()->where('created_at', '>=', now()->subDays(13)->startOfDay())->get(['total', 'created_at'])
            ->groupBy(fn (Order $o) => $o->created_at->toDateString())
            ->map->sum('total');
        $spark = collect(range(13, 0))->map(fn (int $d) => (int) ($daily[now()->subDays($d)->toDateString()] ?? 0) / 1000)->all();

        return [
            Stat::make('Revenue this month', Money::format($revenue, 'en'))
                ->description($change === null ? 'No sales last month' : ($change >= 0 ? "+{$change}% vs last month" : "{$change}% vs last month"))
                ->descriptionIcon($change !== null && $change < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($change !== null && $change < 0 ? 'danger' : 'success')
                ->chart($spark),
            Stat::make('Orders this month', $orders)
                ->description('Average '.($orders ? Money::format(intdiv($revenue, $orders), 'en') : '—'))
                ->descriptionIcon('heroicon-m-shopping-bag'),
            Stat::make('Waiting to ship', Order::whereIn('status', [OrderStatus::Pending->value, OrderStatus::Processing->value])->count())
                ->description(Order::where('status', OrderStatus::Pending->value)->count().' new')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Low stock', Product::where('stock', '<=', Product::LOW_STOCK)->count())
                ->description(Product::where('stock', 0)->count().' out of stock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
