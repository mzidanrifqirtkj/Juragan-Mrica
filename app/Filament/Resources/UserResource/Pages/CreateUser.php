<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Farmer;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $farmerId = request()->integer('farmer_id');

        if (! $farmerId) {
            return;
        }

        $farmer = Farmer::query()->find($farmerId);

        if (! $farmer) {
            return;
        }

        $this->form->fill([
            'name' => $farmer->name,
            'username' => User::generateUniqueUsername($farmer->name, $farmer->phone),
            'email' => '',
            'role' => 'petani',
            'farmer_id' => $farmer->getKey(),
            'is_active' => true,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
