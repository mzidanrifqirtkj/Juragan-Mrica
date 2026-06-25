<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Filament\Pages\Reports\Widgets\Concerns\HasReportPeriod;
use App\Models\Farmer;
use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class TopFarmersTableWidget extends BaseWidget
{
    use HasReportPeriod;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): ?string
    {
        return 'Top 5 Petani';
    }

    protected function getTableDescription(): ?string
    {
        return 'Kontribusi dihitung dari total berat setoran pada periode aktif.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Petani')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('farmer_code')
                    ->label('Kode')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('transactions_sum_weight_kg')
                    ->label('Total Setoran')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('contribution')
                    ->label('Kontribusi')
                    ->state(fn (Farmer $record): float => $this->getTotalPurchaseWeight() > 0
                        ? (($record->transactions_sum_weight_kg ?? 0) / $this->getTotalPurchaseWeight()) * 100
                        : 0)
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->alignEnd()
                    ->badge()
                    ->color('info'),
            ])
            ->striped()
            ->paginated(false)
            ->emptyStateHeading('Belum ada data petani')
            ->emptyStateDescription('Ranking petani akan muncul setelah ada setoran pada periode ini.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        [$start, $end] = $this->getPeriod();

        return Farmer::query()
            ->whereHas('transactions', fn ($query) => $query->whereBetween('transaction_date', [$start, $end]))
            ->withSum([
                'transactions' => fn ($query) => $query->whereBetween('transaction_date', [$start, $end]),
            ], 'weight_kg')
            ->orderByDesc('transactions_sum_weight_kg')
            ->limit(5);
    }

    protected function getTotalPurchaseWeight(): float
    {
        [$start, $end] = $this->getPeriod();

        return (float) Transaction::query()
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('weight_kg');
    }
}
