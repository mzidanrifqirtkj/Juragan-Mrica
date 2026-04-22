<?php

namespace App\Filament\Widgets;

use App\Models\Setting;
use App\Services\InventoryService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockAlertWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return InventoryService::isNearTarget()
            || InventoryService::hasReachedTarget()
            || InventoryService::isLowStock();
    }

    protected function getStats(): array
    {
        $currentStock = InventoryService::getCurrentStock();
        $targetStock = (float) Setting::get('target_stock', 1000);

        if (InventoryService::hasReachedTarget()) {
            return [
                Stat::make('Target Stok Tercapai', number_format($currentStock, 2) . ' kg')
                    ->description('Stok sudah melewati target ' . number_format($targetStock, 0) . ' kg. Siap membuat penjualan bulk.')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success')
                    ->url(route('filament.admin.resources.sales.create')),
            ];
        }

        if (InventoryService::isLowStock()) {
            return [
                Stat::make('Stok Gudang Rendah', number_format($currentStock, 2) . ' kg')
                    ->description('Segera input setoran agar stok aman untuk penjualan berikutnya.')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
                    ->url(route('filament.admin.resources.transactions.create')),
            ];
        }

        return [
            Stat::make('Hampir Mencapai Target', number_format(max($targetStock - $currentStock, 0), 2) . ' kg lagi')
                ->description('Persiapkan penjualan bulk karena stok sudah mendekati target.')
                ->descriptionIcon('heroicon-o-bell-alert')
                ->color('warning'),
        ];
    }
}
