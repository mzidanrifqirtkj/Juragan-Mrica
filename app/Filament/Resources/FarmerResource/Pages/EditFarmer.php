<?php

namespace App\Filament\Resources\FarmerResource\Pages;

use App\Filament\Resources\FarmerResource;
use App\Support\Access;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFarmer extends EditRecord
{
    protected static string $resource = FarmerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\Action::make('create_user')
                ->label('Buatkan Akun')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->url(fn (): string => FarmerResource::getCreateUserUrl($this->record))
                ->visible(fn (): bool => FarmerResource::canCreateUser($this->record)),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => Access::can('farmers.delete')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data petani diperbarui')
            ->body('Perubahan telah tersimpan.');
    }
}
