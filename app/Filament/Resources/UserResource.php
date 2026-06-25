<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Farmer;
use App\Models\User;
use App\Support\Access;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Data Pengguna';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengguna')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->dehydrateStateUsing(fn ($state) => strtolower((string) $state))
                            ->helperText('Gunakan huruf kecil, angka, atau tanda hubung. Username dipakai untuk login.')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->rule('nullable')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->helperText('Boleh dikosongkan. Jika diisi, gunakan email yang aktif.')
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8)
                            ->helperText(
                                fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : ''
                            ),

                        Select::make('role')
                            ->label('Role')
                            ->options([
                                'owner' => 'Owner',
                                'admin' => 'Admin',
                                'petani' => 'Petani',
                            ])
                            ->required()
                            ->live()
                            ->default('petani'),

                        Select::make('farmer_id')
                            ->label('Profil Petani')
                            ->options(function (?User $record): array {
                                return Farmer::query()
                                    ->where(function ($query) use ($record) {
                                        $query->whereDoesntHave('user');

                                        if ($record?->farmer_id) {
                                            $query->orWhere('id', $record->farmer_id);
                                        }
                                    })
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (Farmer $farmer) => [
                                        $farmer->id => "{$farmer->name} ({$farmer->farmer_code})",
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->hidden(fn (): bool => ! Access::hasFarmerLinking())
                            ->visible(fn (Get $get): bool => $get('role') === 'petani')
                            ->required(fn (Get $get): bool => $get('role') === 'petani')
                            ->helperText('Wajib dipilih agar pengguna petani hanya melihat data setoran miliknya.'),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Pengguna tidak aktif tidak dapat login'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'owner' => 'danger',
                        'admin' => 'warning',
                        'petani' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('farmer.name')
                    ->label('Profil Petani')
                    ->description(fn (User $record) => $record->farmer?->farmer_code)
                    ->placeholder('-')
                    ->visible(fn (): bool => Access::hasFarmerLinking())
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'owner' => 'Owner',
                        'admin' => 'Admin',
                        'petani' => 'Petani',
                    ]),

                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->visible(fn (): bool => static::canEdit(new User)),
                Actions\Action::make('toggle_active')
                    ->label(fn (User $record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => Access::can('users.custom') && $record->role !== 'owner')
                    ->action(fn (User $record) => $record->update(['is_active' => ! $record->is_active])),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ! Access::petani() && Access::can('users.view');
    }

    public static function canViewAny(): bool
    {
        return ! Access::petani() && Access::can('users.view');
    }

    public static function canView(Model $record): bool
    {
        return ! Access::petani() && Access::can('users.view');
    }

    public static function canCreate(): bool
    {
        return ! Access::petani() && Access::can('users.create');
    }

    public static function canEdit(Model $record): bool
    {
        return ! Access::petani() && Access::can('users.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return ! Access::petani() && Access::can('users.delete') && $record->role !== 'owner';
    }
}
