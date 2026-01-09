<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Services\InventoryService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data[ 'created_by' ] = Auth::id();
        $data[ 'total_amount' ] = (float) $data[ 'weight_kg' ] * (float) $data[ 'price_per_kg' ];

        return $data;
    }

    protected function beforeCreate(): void
    {
        $weight = (float) $this->data[ 'weight_kg' ];
        $currentStock = InventoryService::getCurrentStock();

        if ($weight > $currentStock) {
            Notification::make()
                ->danger()
                ->title('Stok tidak mencukupi')
                ->body("Berat penjualan ({$weight} kg) melebihi stok saat ini ({$currentStock} kg)")
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Penjualan berhasil dicatat')
            ->body('Data penjualan telah tersimpan dan stok gudang terupdate.');
    }
}
