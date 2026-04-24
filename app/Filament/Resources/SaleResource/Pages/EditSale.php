<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Support\Access;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => Access::can('sales.delete')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data[ 'total_amount' ] = (float) $data[ 'weight_kg' ] * (float) $data[ 'price_per_kg' ];

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data penjualan diperbarui')
            ->body('Perubahan telah tersimpan.');
    }
}
