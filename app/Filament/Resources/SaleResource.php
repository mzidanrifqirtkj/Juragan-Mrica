<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Services\InventoryService;
use App\Support\Access;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Pindah ke Gudang';

    protected static ?string $modelLabel = 'Pindah ke Gudang';

    protected static ?string $pluralModelLabel = 'Data Pindah ke Gudang';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $inventoryService = app(InventoryService::class);
        $currentStock = $inventoryService->getCurrentStock();

        return $schema
            ->components([
                    Section::make('Data Pindah ke Gudang')
                        ->description("Stok saat ini: {$currentStock} kg")
                        ->schema([
                                Select::make('sale_type')
                                    ->label('Tujuan Pindah')
                                    ->options([
                                            'warehouse' => 'Gudang',
                                            'market' => 'Pasar',
                                            'retail' => 'Eceran',
                                        ])
                                    ->default('warehouse')
                                    ->required()
                                    ->live(),

                                Select::make('transaction_id')
                                    ->label('Setoran Petani')
                                    ->relationship('transaction', 'transaction_code', fn(Builder $query) => $query->where('payment_status', 'paid'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Pilih setoran yang akan dipindahkan'),

                                TextInput::make('buyer_name')
                                    ->label('Nama Pembeli / Tujuan')
                                    ->maxLength(255),

                                TextInput::make('buyer_phone')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('weight_kg')
                                    ->label('Berat (Kg)')
                                    ->required()
                                    ->maxValue($currentStock)
                                    ->suffix('kg')
                                    ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                                    ->stripCharacters('.')
                                    ->dehydrateStateUsing(fn($state) => floatval(str_replace(',', '.', $state)))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $weight = floatval(str_replace(',', '.', str_replace('.', '', $state)));
                                        $price = floatval(str_replace(',', '.', str_replace('.', '', $get('price_per_kg'))));
                                        $total = round($weight * $price, 0);
                                        $set('total_amount', number_format($total, 0, ',', '.'));
                                    })
                                    ->helperText("Maksimal: {$currentStock} kg"),

                                TextInput::make('price_per_kg')
                                    ->label('Harga per Kg')
                                    ->required()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                    ->stripCharacters('.')
                                    ->dehydrateStateUsing(fn($state) => floatval(str_replace(',', '.', str_replace('.', '', $state))))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $weight = floatval(str_replace(',', '.', str_replace('.', '', $get('weight_kg'))));
                                        $price = floatval(str_replace(',', '.', str_replace('.', '', $state)));
                                        $total = round($weight * $price, 0);
                                        $set('total_amount', number_format($total, 0, ',', '.'));
                                    })
                                    ->default(70000),

                                // TextInput::make('total_amount')
                                //     ->label('Total Bayar')
                                //     ->required()
                                //     ->prefix('Rp')
                                //     ->formatStateUsing(fn($state) => $state ? number_format(floatval($state), 0, ',', '.') : '')
                                //     ->dehydrateStateUsing(fn($state) => floatval(str_replace(',', '.', str_replace('.', '', $state))))
                                //     ->readonly()
                                //     ->dehydrated(true),

                                TextInput::make('total_amount')
                                    ->label('Total Bayar')
                                    ->required()
                                    ->prefix('Rp ')
                                    ->formatStateUsing(fn($state) => $state ? number_format(floatval($state), 0, ',', '.') : '')
                                    ->dehydrateStateUsing(fn($state) => floatval(str_replace(',', '.', str_replace('.', '', $state))))
                                    ->readonly()
                                    ->dehydrated(true),

                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ])->columns(2),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    Tables\Columns\TextColumn::make('sale_code')
                        ->label('Kode')
                        ->searchable()
                        ->sortable()
                        ->badge()
                        ->color('success'),

                    Tables\Columns\TextColumn::make('sale_type')
                        ->label('Tujuan')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'warehouse' => 'success',
                            'market'    => 'primary',
                            'retail'    => 'warning',
                            default     => 'gray',
                        })
                        ->formatStateUsing(fn(string $state) => match ($state) {
                            'warehouse' => 'Gudang',
                            'market'    => 'Pasar',
                            'retail'    => 'Eceran',
                            default     => $state,
                        }),

                    Tables\Columns\TextColumn::make('buyer_name')
                        ->label('Pembeli')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('weight_kg')
                        ->label('Berat')
                        ->numeric(decimalPlaces: 2)
                        ->suffix(' kg')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('price_per_kg')
                        ->label('Harga/Kg')
                        ->money('IDR')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('total_amount')
                        ->label('Total')
                        ->money('IDR')
                        ->sortable()
                        ->weight('bold'),

                    Tables\Columns\TextColumn::make('sale_date')
                        ->label('Tanggal')
                        ->dateTime('d M Y H:i')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('user.name')
                        ->label('Petani')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ->filters([
                    Tables\Filters\SelectFilter::make('sale_type')
                        ->label('Tujuan')
                        ->options([
                                'warehouse' => 'Gudang',
                                'market' => 'Pasar',
                                'retail' => 'Eceran',
                            ]),

                    Tables\Filters\Filter::make('sale_date')
                        ->form([
                                DatePicker::make('from')
                                    ->label('Dari Tanggal'),
                                DatePicker::make('until')
                                    ->label('Sampai Tanggal'),
                            ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when($data[ 'from' ], fn(Builder $q, $date) => $q->whereDate('sale_date', '>=', $date))
                                ->when($data[ 'until' ], fn(Builder $q, $date) => $q->whereDate('sale_date', '<=', $date));
                        }),
                ])
            ->actions([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make()
                        ->visible(fn (): bool => static::canEdit(new Sale())),
                ])
            ->bulkActions([
                    Actions\BulkActionGroup::make([
                        Actions\DeleteBulkAction::make()
                            ->visible(fn (): bool => Access::can('sales.delete')),
                    ]),
                ])
            ->defaultSort('sale_date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                    Section::make('Detail Penjualan')
                        ->schema([
                                Infolists\Components\TextEntry::make('sale_code')
                                    ->label('Kode Penjualan')
                                    ->badge()
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('sale_type')
                                    ->label('Tujuan')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state) => match ($state) {
                                        'warehouse' => 'Gudang',
                                        'market'    => 'Pasar',
                                        'retail'    => 'Eceran',
                                        default     => $state,
                                    }),
                                Infolists\Components\TextEntry::make('transaction.transaction_code')
                                    ->label('Setoran dari Petani'),
                                Infolists\Components\TextEntry::make('buyer_name')
                                    ->label('Nama Pembeli / Tujuan'),
                                Infolists\Components\TextEntry::make('buyer_phone')
                                    ->label('Telepon'),
                                Infolists\Components\TextEntry::make('weight_kg')
                                    ->label('Berat')
                                    ->suffix(' kg'),
                                Infolists\Components\TextEntry::make('price_per_kg')
                                    ->label('Harga per Kg')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('total_amount')
                                    ->label('Total Bayar')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('sale_date')
                                    ->label('Tanggal Penjualan')
                                    ->dateTime('d M Y H:i'),
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Petani'),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->columnSpanFull(),
                            ])->columns(3),
                ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('sale_date', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ! Access::petani() && Access::can('sales.view');
    }

    public static function canViewAny(): bool
    {
        return ! Access::petani() && Access::can('sales.view');
    }

    public static function canView(Model $record): bool
    {
        return ! Access::petani() && Access::can('sales.view');
    }

    public static function canCreate(): bool
    {
        return ! Access::petani() && Access::can('sales.create');
    }

    public static function canEdit(Model $record): bool
    {
        return ! Access::petani() && Access::can('sales.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return ! Access::petani() && Access::can('sales.delete');
    }
}
