<?php

namespace App\Filament\Resources\FarmerResource\Pages;

use App\Filament\Resources\FarmerResource;
use App\Support\Access;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFarmers extends ListRecords
{
    protected static string $resource = FarmerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Petani')
                ->visible(fn (): bool => Access::can('farmers.create')),
        ];
    }
}
