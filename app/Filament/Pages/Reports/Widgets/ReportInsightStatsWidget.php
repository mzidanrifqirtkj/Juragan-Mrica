<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Filament\Pages\Reports\Widgets\Concerns\HasReportPeriod;
use App\Models\Sale;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportInsightStatsWidget extends BaseWidget
{
    use HasReportPeriod;

    protected function getStats(): array
    {
        [$start, $end] = $this->getPeriod();

        $purchases = Transaction::query()->whereBetween('transaction_date', [$start, $end]);
        $sales = Sale::query()->whereBetween('sale_date', [$start, $end]);

        $totalPurchaseWeight = (clone $purchases)->sum('weight_kg');
        $totalPurchaseAmount = (clone $purchases)->sum('total_amount');
        $totalSalesWeight = (clone $sales)->sum('weight_kg');
        $totalSalesAmount = (clone $sales)->sum('total_amount');

        $avgBuyPrice = $totalPurchaseWeight > 0 ? $totalPurchaseAmount / $totalPurchaseWeight : 0;
        $avgSellPrice = $totalSalesWeight > 0 ? $totalSalesAmount / $totalSalesWeight : 0;
        $profitMargin = $totalSalesAmount > 0 ? (($totalSalesAmount - $totalPurchaseAmount) / $totalSalesAmount) * 100 : 0;

        return [
            Stat::make('Margin Penjualan', number_format($profitMargin, 1).'%')
                ->description('Margin kotor terhadap total penjualan')
                ->descriptionIcon($profitMargin >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profitMargin >= 0 ? 'success' : 'danger'),

            Stat::make('Harga Beli Rata-rata', 'Rp '.number_format($avgBuyPrice, 0, ',', '.'))
                ->description(number_format($totalPurchaseWeight, 2).' kg total pembelian')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('Harga Jual Rata-rata', 'Rp '.number_format($avgSellPrice, 0, ',', '.'))
                ->description(number_format($totalSalesWeight, 2).' kg total penjualan')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success'),

            Stat::make('Berat Terjual', number_format($totalSalesWeight, 2).' kg')
                ->description('Volume penjualan pada periode aktif')
                ->descriptionIcon('heroicon-m-scale')
                ->color('warning'),
        ];
    }
}
