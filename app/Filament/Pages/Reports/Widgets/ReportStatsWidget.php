<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Transaction;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Reactive;

/**
 * Widget Statistik Ringkasan untuk halaman Laporan
 * 
 * Menampilkan 4 stat card utama:
 * - Total Pembelian
 * - Total Penjualan
 * - Laba Kotor
 * - Selisih Harga/kg
 */
class ReportStatsWidget extends BaseWidget
{
    // Reactive properties dari parent page
    #[Reactive]
    public ?string $startDate = null;

    #[Reactive]
    public ?string $endDate = null;

    protected function getStats(): array
    {
        $start = Carbon::parse($this->startDate ?? now()->startOfMonth());
        $end = Carbon::parse($this->endDate ?? now());

        // Data Pembelian (dari petani)
        $purchases = Transaction::whereBetween('transaction_date', [$start, $end]);
        $totalPurchaseAmount = (clone $purchases)->sum('total_amount');
        $totalPurchaseWeight = (clone $purchases)->sum('weight_kg');
        $purchaseCount = (clone $purchases)->count();
        $avgBuyPrice = $totalPurchaseWeight > 0 ? $totalPurchaseAmount / $totalPurchaseWeight : 0;

        // Data Penjualan
        $sales = Sale::whereBetween('sale_date', [$start, $end]);
        $totalSalesAmount = (clone $sales)->sum('total_amount');
        $totalSalesWeight = (clone $sales)->sum('weight_kg');
        $salesCount = (clone $sales)->count();
        $avgSellPrice = $totalSalesWeight > 0 ? $totalSalesAmount / $totalSalesWeight : 0;

        // Kalkulasi Laba
        $grossProfit = $totalSalesAmount - $totalPurchaseAmount;
        $profitMargin = $totalSalesAmount > 0 ? ($grossProfit / $totalSalesAmount) * 100 : 0;
        $priceGap = $avgSellPrice - $avgBuyPrice;

        return [
            Stat::make('Total Pembelian', 'Rp ' . number_format($totalPurchaseAmount, 0, ',', '.'))
                ->description(number_format($totalPurchaseWeight, 2) . ' kg • ' . $purchaseCount . ' transaksi')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info')
                ->chart($this->getPurchaseChartData($start, $end)),

            Stat::make('Total Penjualan', 'Rp ' . number_format($totalSalesAmount, 0, ',', '.'))
                ->description(number_format($totalSalesWeight, 2) . ' kg • ' . $salesCount . ' transaksi')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->chart($this->getSalesChartData($start, $end)),

            Stat::make('Laba Kotor', 'Rp ' . number_format(abs($grossProfit), 0, ',', '.'))
                ->description(($grossProfit >= 0 ? '+' : '-') . number_format(abs($profitMargin), 1) . '% margin')
                ->descriptionIcon($grossProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($grossProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Selisih Harga/kg', 'Rp ' . number_format($priceGap, 0, ',', '.'))
                ->description('Beli: Rp ' . number_format($avgBuyPrice, 0, ',', '.') . ' | Jual: Rp ' . number_format($avgSellPrice, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-scale')
                ->color($priceGap >= 0 ? 'warning' : 'danger'),
        ];
    }

    /**
     * Data chart mini untuk pembelian (7 hari terakhir dari periode)
     */
    protected function getPurchaseChartData(Carbon $start, Carbon $end): array
    {
        $data = [];
        $days = min(7, $start->diffInDays($end) + 1);
        $current = $end->copy()->subDays($days - 1);

        for ($i = 0; $i < $days; $i++) {
            $data[] = Transaction::whereDate('transaction_date', $current)->sum('total_amount') / 1000;
            $current->addDay();
        }

        return $data;
    }

    /**
     * Data chart mini untuk penjualan (7 hari terakhir dari periode)
     */
    protected function getSalesChartData(Carbon $start, Carbon $end): array
    {
        $data = [];
        $days = min(7, $start->diffInDays($end) + 1);
        $current = $end->copy()->subDays($days - 1);

        for ($i = 0; $i < $days; $i++) {
            $data[] = Sale::whereDate('sale_date', $current)->sum('total_amount') / 1000;
            $current->addDay();
        }

        return $data;
    }
}
