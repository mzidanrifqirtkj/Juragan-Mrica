<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Setoran Terbaru';

    protected static ?string $description = '10 transaksi setoran terakhir';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with('farmer')
                    ->latest('transaction_date')
                    ->limit(10)
            )
            ->columns([
                    Tables\Columns\TextColumn::make('transaction_code')
                        ->label('Kode')
                        ->badge()
                        ->color('primary')
                        ->copyable()
                        ->copyMessage('Kode disalin!')
                        ->searchable(),

                    Tables\Columns\TextColumn::make('farmer.name')
                        ->label('Petani')
                        ->description(fn(Transaction $record) => $record->farmer?->farmer_code)
                        ->searchable(),

                    Tables\Columns\TextColumn::make('transaction_date')
                        ->label('Tanggal')
                        ->date('d M Y')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('weight_kg')
                        ->label('Berat')
                        ->numeric(decimalPlaces: 2)
                        ->suffix(' kg')
                        ->alignEnd()
                        ->color('primary'),

                    Tables\Columns\TextColumn::make('total_amount')
                        ->label('Total')
                        ->money('IDR')
                        ->alignEnd()
                        ->weight('bold'),

                    Tables\Columns\TextColumn::make('payment_status')
                        ->label('Status')
                        ->badge()
                        ->colors([
                                'warning' => 'pending',
                                'success' => 'paid',
                            ])
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            'paid'  => '✓ Lunas',
                            default => '○ Belum Bayar',
                        }),
                ])
            ->striped()
            ->paginated(false)
            ->emptyStateHeading('Belum ada setoran')
            ->emptyStateDescription('Transaksi setoran dari petani akan muncul di sini')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
