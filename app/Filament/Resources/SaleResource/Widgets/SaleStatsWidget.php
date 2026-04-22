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

        $warehouseMonth = Sale::warehouse()->thisMonth();
        $marketMonth = Sale::query()->where('sale_type', 'market')
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);
        $retailMonth = Sale::retail()->thisMonth();

        return [
            Stat::make('Penjualan Hari Ini', number_format($today->sum('weight_kg'), 2).' kg')
                ->description('Rp '.number_format($today->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success'),

            Stat::make('Gudang Bulan Ini', number_format($warehouseMonth->sum('weight_kg'), 2).' kg')
                ->description('Rp '.number_format($warehouseMonth->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('success'),

            Stat::make('Pasar Bulan Ini', number_format($marketMonth->sum('weight_kg'), 2).' kg')
                ->description('Rp '.number_format($marketMonth->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('Eceran Bulan Ini', number_format($retailMonth->sum('weight_kg'), 2).' kg')
                ->description('Rp '.number_format($retailMonth->sum('total_amount'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
        ];
    }
}
