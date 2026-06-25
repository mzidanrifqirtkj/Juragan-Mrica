<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Support\Access;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => Access::can('transactions.delete')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total_amount'] = (float) $data['weight_kg'] * (float) $data['price_per_kg'];

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
            ->title('Data setoran diperbarui')
            ->body('Perubahan telah tersimpan.');
    }
}
