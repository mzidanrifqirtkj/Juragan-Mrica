<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\Access;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Setoran Terbaru';

    protected static ?string $description = '10 transaksi setoran terakhir';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Access::can('transactions.view') && Access::petaniConfigured();
    }

    public function table(Table $table): Table
    {
        $query = Access::restrictPetaniTransactionQuery(Transaction::query())
            ->with('farmer')
            ->latest('transaction_date')
            ->limit(10);

        return $table
            ->query($query)
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
                        ->visible(fn (): bool => ! Access::petani())
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
            ->emptyStateHeading(Access::petani() ? 'Belum ada setoran pribadi' : 'Belum ada setoran')
            ->emptyStateDescription(Access::petani() ? 'Setoran Anda akan muncul di sini setelah dicatat admin.' : 'Transaksi setoran dari petani akan muncul di sini')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
