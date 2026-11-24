<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        $counts = Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all' => Tab::make('All'),
            ...collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status) => [
                $status->value => Tab::make($status->getLabel())
                    ->badge($counts[$status->value] ?? null)
                    ->badgeColor($status->getColor())
                    ->modifyQueryUsing(fn (Builder $q) => $q->where('status', $status->value)),
            ])->all(),
        ];
    }
}
