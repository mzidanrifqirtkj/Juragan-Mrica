<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Filament\Pages\Reports\Widgets\Concerns\HasReportPeriod;
use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class SalesChannelsTableWidget extends BaseWidget
{
    use HasReportPeriod;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): ?string
    {
        return 'Breakdown Penjualan per Kanal';
    }

    protected function getTableDescription(): ?string
    {
        return 'Perbandingan nilai, kontribusi, berat, dan transaksi tiap kanal penjualan.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->getChannelRecords())
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Kanal')
                    ->badge()
                    ->color(fn (array $record): string => $record['color']),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nilai Penjualan')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('contribution')
                    ->label('Kontribusi')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->badge()
                    ->color(fn (array $record): string => $record['color'])
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Berat')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('count')
                    ->label('Transaksi')
                    ->numeric()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('avg_price')
                    ->label('Rata-rata / kg')
                    ->money('IDR')
                    ->alignEnd(),
            ])
            ->striped()
            ->paginated(false)
            ->emptyStateHeading('Belum ada penjualan')
            ->emptyStateDescription('Breakdown kanal akan muncul setelah ada penjualan pada periode ini.')
            ->emptyStateIcon('heroicon-o-chart-bar-square');
    }

    protected function getChannelRecords(): Collection
    {
        [$start, $end] = $this->getPeriod();

        $totalSalesAmount = Sale::query()
            ->whereBetween('sale_date', [$start, $end])
            ->sum('total_amount');

        if ($totalSalesAmount <= 0) {
            return collect();
        }

        $channels = [
            ['key' => 'warehouse', 'label' => 'Gudang', 'color' => 'success'],
            ['key' => 'market', 'label' => 'Pasar', 'color' => 'info'],
            ['key' => 'retail', 'label' => 'Eceran', 'color' => 'warning'],
        ];

        return collect($channels)->map(function (array $channel) use ($start, $end, $totalSalesAmount): array {
            $query = Sale::query()
                ->where('sale_type', $channel['key'])
                ->whereBetween('sale_date', [$start, $end]);

            $weight = (clone $query)->sum('weight_kg');
            $amount = (clone $query)->sum('total_amount');
            $count = (clone $query)->count();

            return [
                'key' => $channel['key'],
                'label' => $channel['label'],
                'color' => $channel['color'],
                'amount' => $amount,
                'contribution' => $totalSalesAmount > 0 ? ($amount / $totalSalesAmount) * 100 : 0,
                'weight' => $weight,
                'count' => $count,
                'avg_price' => $weight > 0 ? $amount / $weight : 0,
            ];
        });
    }
}
