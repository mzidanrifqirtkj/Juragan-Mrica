<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Support\RolePermissionMatrix;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $grouped = [];

        foreach (RolePermissionMatrix::groupedOptions() as $feature => $group) {
            $grouped[$feature] = $this->record->permissions
                ->pluck('name')
                ->filter(fn (string $permission) => str_starts_with($permission, "{$feature}."))
                ->values()
                ->all();
        }

        $data['permission_groups'] = $grouped;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $selectedPermissions = collect($data['permission_groups'] ?? [])
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        $record->syncPermissions($selectedPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Permission role berhasil diperbarui');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
