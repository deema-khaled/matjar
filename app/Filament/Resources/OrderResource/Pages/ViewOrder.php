<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            OrderResource::advanceAction(Action::class),
            Action::make('cancel')
                ->label('Cancel order')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalDescription('The items go back into stock. The customer can see the cancellation when tracking the order.')
                ->visible(fn (Order $record) => ! in_array($record->status, [OrderStatus::Cancelled, OrderStatus::Delivered], true))
                ->action(function (Order $record) {
                    $record->cancel();
                    Notification::make()->title("{$record->number} cancelled, stock restored")->success()->send();
                }),
        ];
    }
}
