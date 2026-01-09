<?php

namespace App\Filament\Resources\InventoryResource\Widgets;

use App\Services\InventoryService;
use App\Models\Setting;
use App\Models\InventoryLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentStockWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $currentStock = InventoryService::getCurrentStock();
        $targetStock = Setting::get('target_stock', 1000);
        $percentage = InventoryService::getStockPercentage();
        $status = InventoryService::getStockStatus();

        $stockIn = InventoryLog::stockIn()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('weight_kg');

        $stockOut = InventoryLog::stockOut()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('weight_kg');

        $estimatedDays = InventoryService::getEstimatedDaysToTarget();

        return [
            Stat::make('Stok Gudang Saat Ini', number_format($currentStock, 2) . ' kg')
                ->description($status[ 'message' ])
                ->descriptionIcon($status[ 'icon' ])
                ->color($status[ 'color' ])
                ->chart([
                    (int) $currentStock,
                    (int) $targetStock,
                ]),

            Stat::make('Progress ke 1 Ton', number_format($percentage, 1) . '%')
                ->description(number_format($targetStock - $currentStock, 2) . ' kg lagi ke target')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($percentage >= 90 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger')),

            Stat::make('Masuk Bulan Ini', '+' . number_format($stockIn, 2) . ' kg')
                ->description('Total stok masuk')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Keluar Bulan Ini', '-' . number_format($stockOut, 2) . ' kg')
                ->description('Total stok keluar')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),
        ];
    }
}
