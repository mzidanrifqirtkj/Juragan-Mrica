<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Transaction::whereDate('transaction_date', today());
        $thisMonth = Transaction::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);

        return [
            Stat::make('Setoran Hari Ini', number_format($today->sum('weight_kg'), 2) . ' kg')
                ->description($today->count() . ' transaksi')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Total Bayar Hari Ini', 'Rp ' . number_format($today->sum('total_amount'), 0, ',', '.'))
                ->description('Tunai & Transfer')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Setoran Bulan Ini', number_format($thisMonth->sum('weight_kg'), 2) . ' kg')
                ->description($thisMonth->count() . ' transaksi')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Total Bayar Bulan Ini', 'Rp ' . number_format($thisMonth->sum('total_amount'), 0, ',', '.'))
                ->description('Akumulasi bulan ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
