<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Transaction;
use App\Services\InventoryService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['total_amount'] = (float) $data['weight_kg'] * (float) $data['price_per_kg'];

        // Set sale_date if not provided
        if (empty($data['sale_date'])) {
            $data['sale_date'] = now();
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        $weight = (float) $this->data['weight_kg'];
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

    protected function afterCreate(): void
    {
        // Get all unsold transactions
        $unsoldTransactions = Transaction::unsold()->get();

        if ($unsoldTransactions->isEmpty()) {
            return;
        }

        // Prepare data for pivot table
        $pivotData = [];
        foreach ($unsoldTransactions as $transaction) {
            $pivotData[$transaction->id] = [
                'weight_kg' => $transaction->weight_kg,
            ];
        }

        // Attach transactions to this sale via pivot table
        $this->record->transactions()->attach($pivotData);

        // Mark all these transactions as sold
        Transaction::whereIn('id', $unsoldTransactions->pluck('id'))
            ->update(['is_sold' => true]);

    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        $transactionCount = $this->record->transactions()->count();

        return Notification::make()
            ->success()
            ->title('Penjualan berhasil dicatat')
            ->body("Data penjualan telah tersimpan. {$transactionCount} setoran petani telah diproses.");
    }
}
