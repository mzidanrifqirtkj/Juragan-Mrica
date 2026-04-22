<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Sale;
use App\Models\Farmer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    // Full width untuk stats cards
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $todayTransactions = Transaction::whereDate('transaction_date', today());
        $todaySales = Sale::whereDate('sale_date', today());

        $monthTransactions = Transaction::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
        $monthSales = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);

        $totalPurchase = (clone $monthTransactions)->sum('total_amount');
        $totalSales = (clone $monthSales)->sum('total_amount');
        $profit = $totalSales - $totalPurchase;

        $todayTransactionsCount = (clone $todayTransactions)->count();
        $todayWeight = (clone $todayTransactions)->sum('weight_kg');
        $todaySalesCount = (clone $todaySales)->count();
        $todaySalesAmount = (clone $todaySales)->sum('total_amount');
        $activeFarmers = Farmer::where('is_active', true)->count();
        $totalFarmers = Farmer::count();

        return [
            Stat::make('Setoran Hari Ini', $todayTransactionsCount . ' transaksi')
                ->description(number_format($todayWeight, 1) . ' kg diterima hari ini')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('primary')
                ->chart($this->getTransactionChartData()),

            Stat::make('Penjualan Hari Ini', $todaySalesCount . ' transaksi')
                ->description('Rp ' . number_format($todaySalesAmount, 0, ',', '.') . ' pendapatan')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->chart($this->getSalesChartData()),

            Stat::make('Petani Aktif', $activeFarmers . ' petani')
                ->description('Dari total ' . $totalFarmers . ' petani terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Laba Bulan Ini', 'Rp ' . number_format(abs($profit), 0, ',', '.'))
                ->description($profit >= 0 ? 'Profit bulan ' . now()->translatedFormat('F') : 'Kerugian bulan ' . now()->translatedFormat('F'))
                ->descriptionIcon($profit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profit >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function getTransactionChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Transaction::whereDate('transaction_date', $date)->count();
        }
        return $data;
    }

    protected function getSalesChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Sale::whereDate('sale_date', $date)->count();
        }
        return $data;
    }
}
