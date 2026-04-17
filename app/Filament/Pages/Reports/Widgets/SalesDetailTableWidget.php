<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Filament\Pages\Reports\Widgets\Concerns\HasReportPeriod;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SalesDetailTableWidget extends BaseWidget
{
    use HasReportPeriod;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): ?string
    {
        return 'Detail Transaksi & Perhitungan Laba';
    }

    protected function getTableDescription(): ?string
    {
        [$start, $end] = $this->getPeriod();

        $sales = Sale::query()
            ->with('transaction:id,price_per_kg')
            ->whereBetween('sale_date', [$start, $end])
            ->get();

        $totalBuyAmount = $sales->sum(fn (Sale $sale): float => ($sale->transaction?->price_per_kg ?? 0) * $sale->weight_kg);
        $totalSellAmount = $sales->sum('total_amount');
        $totalProfit = $sales->sum(fn (Sale $sale): float => ($sale->price_per_kg - ($sale->transaction?->price_per_kg ?? 0)) * $sale->weight_kg);
        $margin = $totalSellAmount > 0 ? ($totalProfit / $totalSellAmount) * 100 : 0;

        if ($sales->isEmpty()) {
            return 'Tidak ada transaksi penjualan pada periode aktif.';
        }

        return 'Modal Rp ' . number_format($totalBuyAmount, 0, ',', '.')
            . ' • Jual Rp ' . number_format($totalSellAmount, 0, ',', '.')
            . ' • Laba Rp ' . number_format($totalProfit, 0, ',', '.')
            . ' • Margin ' . number_format($margin, 1) . '%';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_code')
                    ->label('Kode')
                    ->badge()
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Kode penjualan disalin!')
                    ->searchable(),

                Tables\Columns\TextColumn::make('transaction.farmer.name')
                    ->label('Petani')
                    ->description(fn (Sale $record): string => $record->transaction?->farmer?->farmer_code ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sale_type')
                    ->label('Tujuan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'warehouse' => 'Gudang',
                        'market' => 'Pasar',
                        'retail' => 'Eceran',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'warehouse' => 'success',
                        'market' => 'info',
                        'retail' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Berat')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->alignEnd()
                    ->summarize([
                        Sum::make()->label('Total')->numeric(decimalPlaces: 2)->suffix(' kg'),
                    ]),

                Tables\Columns\TextColumn::make('transaction.price_per_kg')
                    ->label('Beli/kg')
                    ->state(fn (Sale $record): float => $record->transaction?->price_per_kg ?? 0)
                    ->money('IDR')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('price_per_kg')
                    ->label('Jual/kg')
                    ->money('IDR')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('profit_per_kg')
                    ->label('Laba/kg')
                    ->state(fn (Sale $record): float => $record->price_per_kg - ($record->transaction?->price_per_kg ?? 0))
                    ->money('IDR')
                    ->alignEnd()
                    ->color(fn (float $state): string => $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Total Laba')
                    ->state(fn (Sale $record): float => ($record->price_per_kg - ($record->transaction?->price_per_kg ?? 0)) * $record->weight_kg)
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn (float $state): string => $state >= 0 ? 'success' : 'danger'),
            ])
            ->defaultSort('sale_date', 'desc')
            ->striped()
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada transaksi penjualan')
            ->emptyStateDescription('Detail transaksi dan laba akan muncul setelah ada penjualan pada periode ini.')
            ->emptyStateIcon('heroicon-o-inbox');
    }

    protected function getTableQuery(): Builder | Relation | null
    {
        [$start, $end] = $this->getPeriod();

        return Sale::query()
            ->with(['transaction.farmer'])
            ->whereBetween('sale_date', [$start, $end]);
    }
}
