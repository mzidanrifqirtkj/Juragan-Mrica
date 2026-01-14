<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FarmerResource\Pages;
use App\Filament\Resources\FarmerResource\RelationManagers;
use App\Models\Farmer;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FarmerResource extends Resource
{
    protected static ?string $model = Farmer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Petani';

    protected static ?string $modelLabel = 'Petani';

    protected static ?string $pluralModelLabel = 'Data Petani';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Petani')
                    ->description('Data lengkap petani')
                    ->schema([
                        TextInput::make('farmer_code')
                            ->label('Kode Petani')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto generate')
                            ->helperText('Kode akan di-generate otomatis'),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama petani'),

                        TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('08xxxxxxxxxx'),

                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Alamat lengkap petani'),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Petani tidak aktif tidak akan muncul di form setoran'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->maxLength(1000)
                            ->placeholder('Catatan tambahan (opsional)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('farmer_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('Transaksi')
                    ->counts('transactions')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('transactions_sum_weight_kg')
                    ->label('Total Kg')
                    ->sum('transactions', 'weight_kg')
                    ->sortable()
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg'),

                Tables\Columns\TextColumn::make('transactions_sum_total_amount')
                    ->label('Total Bayar')
                    ->sum('transactions', 'total_amount')
                    ->sortable()
                    ->money('IDR'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('toggle_active')
                    ->label(fn(Farmer $record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn(Farmer $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(Farmer $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn(Farmer $record) => $record->update([ 'is_active' => !$record->is_active ])),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn($records) => $records->each->update([ 'is_active' => true ])),
                    Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn($records) => $records->each->update([ 'is_active' => false ])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Petani')
                    ->schema([
                        Infolists\Components\TextEntry::make('farmer_code')
                            ->label('Kode Petani')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nama'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Telepon')
                            ->icon('heroicon-m-phone'),
                        Infolists\Components\TextEntry::make('address')
                            ->label('Alamat'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan'),
                    ])->columns(2),

                Section::make('Statistik')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_count')
                            ->label('Total Transaksi')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('total_weight')
                            ->label('Total Berat')
                            ->suffix(' kg')
                            ->numeric(decimalPlaces: 2),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total Pembayaran')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('average_weight')
                            ->label('Rata-rata per Transaksi')
                            ->suffix(' kg')
                            ->numeric(decimalPlaces: 2),
                    ])->columns(4),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFarmers::route('/'),
            'create' => Pages\CreateFarmer::route('/create'),
            'view' => Pages\ViewFarmer::route('/{record}'),
            'edit' => Pages\EditFarmer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
