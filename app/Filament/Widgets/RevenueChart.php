<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue (JOD), last 30 days';

    // Render with the page instead of waiting until scrolled into view.
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        // Grouped in PHP so it works the same on MySQL and SQLite.
        $daily = Order::sales()
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $o) => $o->created_at->toDateString())
            ->map->sum('total');

        $days = collect(range(29, 0))->map(fn (int $d) => now()->subDays($d));

        return [
            'datasets' => [[
                'label' => 'Revenue',
                'data' => $days->map(fn ($day) => round(($daily[$day->toDateString()] ?? 0) / 1000, 2))->all(),
                'borderColor' => '#0f766e',
                'backgroundColor' => 'rgba(15, 118, 110, 0.12)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $days->map(fn ($day) => $day->format('j M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
