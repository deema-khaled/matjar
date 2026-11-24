<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Support\Money;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(6))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('number')->fontFamily('mono')->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn (int $state) => Money::format($state, 'en')),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Placed')->since(),
            ])
            ->recordUrl(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record]));
    }
}
