<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $recordTitleAttribute = 'number';

    public static function getNavigationBadge(): ?string
    {
        $pending = Order::where('status', OrderStatus::Pending->value)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pending orders';
    }

    public static function canCreate(): bool
    {
        return false; // orders only come from the storefront
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->copyable()->weight('bold')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer')->searchable(['customer_name', 'phone'])
                    ->description(fn (Order $o) => $o->phone),
                Tables\Columns\TextColumn::make('city')->toggleable(),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn (int $state) => Money::format($state, 'en'))->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Placed')->since()->sortable()
                    ->tooltip(fn (Order $o) => $o->created_at->format('j M Y, H:i')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(OrderStatus::class)->multiple(),
                Tables\Filters\SelectFilter::make('city')->options(array_combine(config('store.cities'), config('store.cities'))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                self::advanceAction(Tables\Actions\Action::class),
            ]);
    }

    /**
     * "Mark as processing → shipped → delivered" in one click.
     *
     * @param  class-string<Tables\Actions\Action|Action>  $class
     */
    public static function advanceAction(string $class): Tables\Actions\Action|Action
    {
        $next = fn (Order $o): ?OrderStatus => match ($o->status) {
            OrderStatus::Pending => OrderStatus::Processing,
            OrderStatus::Processing => OrderStatus::Shipped,
            OrderStatus::Shipped => OrderStatus::Delivered,
            default => null,
        };

        return $class::make('advance')
            ->label(fn (Order $record) => $next($record) ? 'Mark '.strtolower($next($record)->getLabel()) : '')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('primary')
            ->visible(fn (Order $record) => $next($record) !== null)
            ->action(function (Order $record) use ($next) {
                $record->update(['status' => $next($record)]);
                Notification::make()->title("{$record->number} → {$record->status->getLabel()}")->success()->send();
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(3)->schema([
            Infolists\Components\Section::make('Items')->columnSpan(2)->schema([
                Infolists\Components\RepeatableEntry::make('items')->hiddenLabel()->columns(4)->schema([
                    Infolists\Components\TextEntry::make('product_name')->label('Product')->columnSpan(2),
                    Infolists\Components\TextEntry::make('quantity')->label('Qty'),
                    Infolists\Components\TextEntry::make('line_total')->label('Total')->formatStateUsing(fn (int $state) => Money::format($state, 'en')),
                ]),
                Infolists\Components\Grid::make(3)->schema([
                    Infolists\Components\TextEntry::make('subtotal')->formatStateUsing(fn (int $state) => Money::format($state, 'en')),
                    Infolists\Components\TextEntry::make('shipping')->formatStateUsing(fn (int $state) => $state === 0 ? 'Free' : Money::format($state, 'en')),
                    Infolists\Components\TextEntry::make('total')->weight('bold')->formatStateUsing(fn (int $state) => Money::format($state, 'en')),
                ]),
            ]),
            Infolists\Components\Group::make()->schema([
                Infolists\Components\Section::make('Order')->schema([
                    Infolists\Components\TextEntry::make('number')->copyable()->fontFamily('mono'),
                    Infolists\Components\TextEntry::make('status')->badge(),
                    Infolists\Components\TextEntry::make('created_at')->label('Placed')->dateTime('j M Y, H:i'),
                    Infolists\Components\TextEntry::make('payment_method')->label('Payment')->formatStateUsing(fn () => 'Cash on delivery'),
                ]),
                Infolists\Components\Section::make('Customer')->schema([
                    Infolists\Components\TextEntry::make('customer_name')->label('Name'),
                    Infolists\Components\TextEntry::make('phone')->copyable(),
                    Infolists\Components\TextEntry::make('email')->placeholder('—'),
                    Infolists\Components\TextEntry::make('city'),
                    Infolists\Components\TextEntry::make('address'),
                    Infolists\Components\TextEntry::make('notes')->placeholder('—'),
                ]),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
