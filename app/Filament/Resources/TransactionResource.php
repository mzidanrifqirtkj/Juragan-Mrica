<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Setting;
use App\Models\Transaction;
use App\Support\Access;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Setoran';

    protected static ?string $modelLabel = 'Setoran';

    protected static ?string $pluralModelLabel = 'Data Setoran';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Setoran')
                    ->description('Input data setoran lada dari petani')
                    ->schema([
                        Select::make('farmer_id')
                            ->label('Petani')
                            ->relationship('farmer', 'name', fn (Builder $query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama Petani')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->required(),
                                Textarea::make('address')
                                    ->label('Alamat')
                                    ->rows(2),
                            ]),

                        TextInput::make('weight_kg')
                            ->label('Berat (Kg)')
                            ->required()
                            ->suffix('kg')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => floatval(str_replace(',', '.', $state)))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $weight = floatval(str_replace(',', '.', str_replace('.', '', $state)));
                                $price = floatval(str_replace(',', '.', str_replace('.', '', $get('price_per_kg'))));
                                $total = round($weight * $price, 0);
                                $set('total_amount', number_format($total, 0, ',', '.'));
                            }),

                        TextInput::make('price_per_kg')
                            ->label('Harga per Kg')
                            ->required()
                            ->prefix('Rp ')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => floatval(str_replace(',', '.', str_replace('.', '', $state))))
                            ->default(Setting::get('default_price_per_kg', 100000))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $weight = floatval(str_replace(',', '.', str_replace('.', '', $get('weight_kg'))));
                                $price = floatval(str_replace(',', '.', str_replace('.', '', $state)));
                                $total = round($weight * $price, 0);
                                $set('total_amount', number_format($total, 0, ',', '.'));
                            }),

                        TextInput::make('total_amount')
                            ->label('Total Bayar')
                            ->required()
                            ->prefix('Rp ')
                            ->formatStateUsing(fn ($state) => $state ? number_format(floatval($state), 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn ($state) => floatval(str_replace(',', '.', str_replace('.', '', $state))))
                            ->readonly()
                            ->dehydrated(true),

                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer',
                            ])
                            ->default('cash')
                            ->required()
                            ->live(),

                        FileUpload::make('payment_proof')
                            ->label('Bukti Transfer')
                            ->image()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('payment-proofs')
                            ->visibility('public')
                            ->visible(fn (Get $get): bool => $get('payment_method') === 'transfer')
                            ->helperText('Upload foto bukti transfer (max 2MB)'),

                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Belum Bayar',
                                'paid' => 'Sudah Bayar',
                            ])
                            ->default('paid')
                            ->required(),

                        DateTimePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->required(),

                        Hidden::make('created_by')
                            ->default(fn () => auth()->id()),

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
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('farmer.name')
                    ->label('Petani')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Berat')
                    ->numeric(decimalPlaces: 2, locale: 'id')
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

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => $state === 'paid' ? 'Lunas' : 'Belum Bayar'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => $state === 'cash' ? 'Tunai' : 'Transfer')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_sold')
                    ->label('Terjual')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Pencatat')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Bayar')
                    ->options([
                        'pending' => 'Belum Bayar',
                        'paid' => 'Lunas',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                    ]),

                Tables\Filters\SelectFilter::make('farmer')
                    ->relationship('farmer', 'name')
                    ->visible(fn (): bool => ! Access::petani())
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('month_year')
                    ->label('Bulan & Tahun')
                    ->options(function () {
                        $months = [];
                        $currentMonth = now();

                        // Generate options untuk 12 bulan ke belakang
                        for ($i = 0; $i < 12; $i++) {
                            $date = $currentMonth->copy()->subMonths($i);
                            $key = $date->format('Y-m');
                            $label = $date->format('F Y');
                            $months[$key] = $label;
                        }

                        return $months;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        [$year, $month] = explode('-', $data['value']);

                        return $query->whereYear('transaction_date', $year)
                            ->whereMonth('transaction_date', $month);
                    }),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('transaction_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('transaction_date', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()
                    ->visible(fn (): bool => static::canEdit(new Transaction)),
                Actions\Action::make('mark_paid')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Transaction $record) => ! Access::petani() && Access::can('transactions.custom') && $record->payment_status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (Transaction $record) => $record->update(['payment_status' => 'paid'])),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => ! Access::petani() && Access::can('transactions.delete')),
                    Actions\BulkAction::make('mark_paid_bulk')
                        ->label('Tandai Lunas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (): bool => ! Access::petani() && Access::can('transactions.custom'))
                        ->action(fn ($records) => $records->each->update(['payment_status' => 'paid'])),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Setoran')
                    ->schema([
                        TextEntry::make('transaction_code')
                            ->label('Kode Transaksi')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('farmer.name')
                            ->label('Nama Petani'),

                        TextEntry::make('farmer.phone')
                            ->label('Telepon Petani'),

                        TextEntry::make('weight_kg')
                            ->label('Berat')
                            ->numeric(decimalPlaces: 2, locale: 'id')
                            ->suffix(' kg'),

                        TextEntry::make('price_per_kg')
                            ->label('Harga per Kg')
                            ->money('IDR'),

                        TextEntry::make('total_amount')
                            ->label('Total Bayar')
                            ->money('IDR'),

                        TextEntry::make('payment_status')
                            ->label('Status Bayar')
                            ->badge()
                            ->color(fn (string $state) => $state === 'paid' ? 'success' : 'warning'),

                        TextEntry::make('payment_method')
                            ->label('Metode Bayar')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => $state === 'cash' ? 'Tunai' : 'Transfer'),

                        TextEntry::make('payment_proof')
                            ->label('Bukti Transfer')
                            ->image()
                            ->visible(fn (Transaction $record): bool => $record->payment_method === 'transfer' && filled($record->payment_proof))
                            ->columnSpanFull(),

                        TextEntry::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('user.name')
                            ->label('Petani'),

                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::scopeEloquentQuery(static::getModel()::query())
            ->where('payment_status', 'pending');

        return $query->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Access::can('transactions.view') && Access::petaniConfigured();
    }

    public static function canViewAny(): bool
    {
        return Access::can('transactions.view') && Access::petaniConfigured();
    }

    public static function canView(Model $record): bool
    {
        return Access::can('transactions.view') && $record instanceof Transaction && Access::ownsTransaction($record);
    }

    public static function canCreate(): bool
    {
        return ! Access::petani() && Access::can('transactions.create');
    }

    public static function canEdit(Model $record): bool
    {
        return ! Access::petani() && Access::can('transactions.edit') && $record instanceof Transaction && Access::ownsTransaction($record);
    }

    public static function canDelete(Model $record): bool
    {
        return ! Access::petani() && Access::can('transactions.delete') && $record instanceof Transaction && Access::ownsTransaction($record);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::scopeEloquentQuery(parent::getEloquentQuery()->with(['farmer', 'creator']));
    }

    protected static function scopeEloquentQuery(Builder $query): Builder
    {
        return Access::restrictPetaniTransactionQuery($query);
    }
}
