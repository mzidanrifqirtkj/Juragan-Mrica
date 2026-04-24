<?php

namespace App\Filament\Resources\FarmerResource\Pages;

use App\Filament\Resources\FarmerResource;
use App\Support\Access;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFarmer extends ViewRecord
{
    protected static string $resource = FarmerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (): bool => Access::can('farmers.edit')),
            Actions\Action::make('create_user')
                ->label('Buatkan Akun')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->url(fn (): string => FarmerResource::getCreateUserUrl($this->record))
                ->visible(fn (): bool => FarmerResource::canCreateUser($this->record)),
        ];
    }
}
