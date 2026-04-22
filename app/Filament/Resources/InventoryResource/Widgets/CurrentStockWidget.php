<?php

namespace App\Filament\Resources\InventoryResource\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithStockStats;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentStockWidget extends BaseWidget
{
    use InteractsWithStockStats;

    protected function getStats(): array
    {
        [
            'currentStock' => $currentStock,
            'targetStock' => $targetStock,
            'percentage' => $percentage,
            'remainingStock' => $remainingStock,
            'status' => $status,
            'estimatedDays' => $estimatedDays,
            'stockIn' => $stockIn,
            'stockOut' => $stockOut,
        ] = $this->getStockStats();

        return [
            Stat::make('Stok Gudang Saat Ini', number_format($currentStock, 2) . ' kg')
                ->description($status['message'])
                ->descriptionIcon($status['icon'])
                ->color($status['color'])
                ->chart([
                    (float) max($currentStock - $stockOut, 0),
                    (float) $currentStock,
                    (float) min($targetStock, max($currentStock, 0)),
                ]),

            Stat::make('Progress ke Target', number_format($percentage, 1) . '%')
                ->description(number_format($remainingStock, 2) . ' kg lagi menuju target ' . number_format($targetStock, 0) . ' kg')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color(match (true) {
                    $percentage >= 100 => 'success',
                    $percentage >= 90 => 'warning',
                    default => 'info',
                }),

            Stat::make('Stok Masuk Bulan Ini', '+' . number_format($stockIn, 2) . ' kg')
                ->description('Akumulasi stok masuk bulan ' . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Stok Keluar Bulan Ini', '-' . number_format($stockOut, 2) . ' kg')
                ->description(
                    $estimatedDays === null
                        ? 'Belum cukup data untuk estimasi target'
                        : ($estimatedDays === 0
                            ? 'Target stok sudah tercapai'
                            : 'Estimasi ' . $estimatedDays . ' hari menuju target')
                )
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),
        ];
    }
}
