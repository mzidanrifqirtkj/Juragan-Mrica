<?php

namespace App\Filament\Widgets;

use App\Models\Farmer;
use App\Models\Sale;
use App\Models\Transaction;
use App\Support\Access;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    // Full width untuk stats cards
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Access::can('transactions.view') && Access::petaniConfigured();
    }

    protected function getStats(): array
    {
        $todayTransactions = Access::restrictPetaniTransactionQuery(Transaction::query())
            ->whereDate('transaction_date', today());

        $monthTransactions = Access::restrictPetaniTransactionQuery(Transaction::query())
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);

        $monthSales = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);

        if (Access::petani()) {
            $pendingTransactions = (clone $monthTransactions)->where('payment_status', 'pending')->count();
            $paidTransactions = (clone $monthTransactions)->where('payment_status', 'paid')->count();
            $monthWeight = (clone $monthTransactions)->sum('weight_kg');
            $monthTotal = (clone $monthTransactions)->sum('total_amount');

            return [
                Stat::make('Setoran Saya Bulan Ini', number_format($monthWeight, 2).' kg')
                    ->description((clone $monthTransactions)->count().' transaksi setoran tercatat')
                    ->descriptionIcon('heroicon-m-arrow-down-tray')
                    ->color('primary')
                    ->chart($this->getTransactionChartData()),

                Stat::make('Nilai Setoran Saya', 'Rp '.number_format($monthTotal, 0, ',', '.'))
                    ->description('Akumulasi nilai setoran bulan '.now()->translatedFormat('F'))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                Stat::make('Menunggu Pembayaran', $pendingTransactions.' transaksi')
                    ->description('Setoran yang belum dibayar')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color($pendingTransactions > 0 ? 'warning' : 'gray'),

                Stat::make('Sudah Dibayar', $paidTransactions.' transaksi')
                    ->description(number_format((clone $todayTransactions)->sum('weight_kg'), 2).' kg disetor hari ini')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('info'),
            ];
        }

        $todaySales = Sale::whereDate('sale_date', today());
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
            Stat::make('Setoran Hari Ini', $todayTransactionsCount.' transaksi')
                ->description(number_format($todayWeight, 1).' kg diterima hari ini')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('primary')
                ->chart($this->getTransactionChartData()),

            Stat::make('Penjualan Hari Ini', $todaySalesCount.' transaksi')
                ->description('Rp '.number_format($todaySalesAmount, 0, ',', '.').' pendapatan')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->chart($this->getSalesChartData()),

            Stat::make('Petani Aktif', $activeFarmers.' petani')
                ->description('Dari total '.$totalFarmers.' petani terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Laba Bulan Ini', 'Rp '.number_format(abs($profit), 0, ',', '.'))
                ->description($profit >= 0 ? 'Profit bulan '.now()->translatedFormat('F') : 'Kerugian bulan '.now()->translatedFormat('F'))
                ->descriptionIcon($profit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profit >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function getTransactionChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Access::restrictPetaniTransactionQuery(Transaction::query())
                ->whereDate('transaction_date', $date)
                ->count();
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
