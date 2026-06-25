<?php

namespace App\Filament\Pages\Auth;

use App\Support\Access;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class MyProfile extends EditProfile
{
    protected static ?string $title = 'Profil Saya';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $farmer = Access::hasFarmerLinking() ? $this->getUser()->farmer : null;

        $data['farmer_code'] = $farmer?->farmer_code;
        $data['farmer_name'] = $farmer?->name;
        $data['farmer_phone'] = $farmer?->phone;
        $data['farmer_address'] = $farmer?->address;
        $data['farmer_notes'] = $farmer?->notes;

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->schema([
                        $this->getNameFormComponent(),
                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->alphaDash()
                            ->dehydrateStateUsing(fn ($state) => strtolower((string) $state))
                            ->unique(ignoreRecord: true)
                            ->helperText('Username ini bisa dipakai untuk login selain email.'),
                        $this->getEmailFormComponent(),
                        TextInput::make('role')
                            ->label('Role')
                            ->formatStateUsing(fn ($state) => ucfirst((string) $state))
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Section::make('Keamanan Akun')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ])
                    ->columns(2),

                Section::make('Profil Petani')
                    ->description('Data petani yang tertaut ke akun ini. Perubahan data inti tetap dikelola admin.')
                    ->visible(fn (): bool => Access::hasFarmerLinking() && $this->getUser()->isPetani())
                    ->schema([
                        TextInput::make('farmer_code')
                            ->label('Kode Petani')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Belum ditautkan'),
                        TextInput::make('farmer_name')
                            ->label('Nama Petani')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Belum ditautkan'),
                        TextInput::make('farmer_phone')
                            ->label('No. Telepon')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('-'),
                        TextInput::make('farmer_address')
                            ->label('Alamat')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->placeholder('-'),
                        Textarea::make('farmer_notes')
                            ->label('Catatan')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Tidak ada catatan.'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->rule('nullable')
            ->email()
            ->unique(ignoreRecord: true)
            ->maxLength(255)
            ->helperText('Boleh dikosongkan. Jika diisi, gunakan email yang aktif.')
            ->live(debounce: 500);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profil berhasil diperbarui';
    }
}
