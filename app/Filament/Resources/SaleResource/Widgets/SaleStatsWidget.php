<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaleStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Sale::whereDate('sale_date', today());
        $thisMonth = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);

        $retailMonth = Sale::retail()->thisMonth();
        $bulkMonth = Sale::bulk()->thisMonth();

        return [
            Stat::make('Penjualan Hari Ini', number_format($today->sum('weight_kg'), 2) . ' kg')
                ->description('Rp ' . number_format($today->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success'),

            Stat::make('Retail Bulan Ini', number_format($retailMonth->sum('weight_kg'), 2) . ' kg')
                ->description('Rp ' . number_format($retailMonth->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),

            Stat::make('Bulk Bulan Ini', number_format($bulkMonth->sum('weight_kg'), 2) . ' kg')
                ->description('Rp ' . number_format($bulkMonth->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('Total Bulan Ini', 'Rp ' . number_format($thisMonth->sum('total_amount'), 0, ',', '.'))
                ->description($thisMonth->count() . ' transaksi')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }
}
