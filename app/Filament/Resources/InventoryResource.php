<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\InventoryLog;
use App\Services\InventoryService;
use App\Support\Access;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class InventoryResource extends Resource
{
    protected static ?string $model = InventoryLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Penyimpanan';

    protected static ?string $modelLabel = 'Log Penyimpanan';

    protected static ?string $pluralModelLabel = 'Log Penyimpanan';

    protected static string|UnitEnum|null $navigationGroup = 'Penyimpanan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        // Read-only resource, no form needed
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => $state === 'in' ? 'Masuk' : 'Keluar'),

                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Berat')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Sumber')
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            'purchase' => 'Setoran',
                            'sale' => 'Penjualan',
                            default => 'Manual'
                        };
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'purchase' => 'info',
                        'sale' => 'warning',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('reference_code')
                    ->label('Kode Referensi')
                    ->getStateUsing(function (InventoryLog $record) {
                        $reference = $record->getReference();

                        return $reference?->transaction_code ?? $reference?->sale_code ?? '-';
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn (InventoryLog $record) => $record->notes),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Saldo Stok')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
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
            'index' => Pages\ListInventories::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ! Access::petani() && Access::can('inventory.view');
    }

    public static function canViewAny(): bool
    {
        return ! Access::petani() && Access::can('inventory.view');
    }

    public static function canView(Model $record): bool
    {
        return ! Access::petani() && Access::can('inventory.view');
    }

    public static function getNavigationBadge(): ?string
    {
        $stock = InventoryService::getCurrentStock();

        return number_format($stock, 0).' kg';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $status = InventoryService::getStockStatus();

        return $status['color'];
    }
}
