<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Sale;
use App\Models\Farmer;
use App\Services\InventoryService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $todayTransactions = Transaction::whereDate('transaction_date', today());
        $todaySales = Sale::whereDate('sale_date', today());

        $monthTransactions = Transaction::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
        $monthSales = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);

        $totalPurchase = $monthTransactions->sum('total_amount');
        $totalSales = $monthSales->sum('total_amount');
        $profit = $totalSales - $totalPurchase;

        return [
            Stat::make('Setoran Hari Ini', $todayTransactions->count())
                ->description(number_format($todayTransactions->sum('weight_kg'), 2) . ' kg')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('primary')
                ->chart($this->getTransactionChartData()),

            Stat::make('Penjualan Hari Ini', $todaySales->count())
                ->description('Rp ' . number_format($todaySales->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->chart($this->getSalesChartData()),

            Stat::make('Petani Aktif', Farmer::where('is_active', true)->count())
                ->description('Total terdaftar: ' . Farmer::count())
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Laba Bulan Ini', 'Rp ' . number_format($profit, 0, ',', '.'))
                ->description($profit >= 0 ? 'Profit' : 'Loss')
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
