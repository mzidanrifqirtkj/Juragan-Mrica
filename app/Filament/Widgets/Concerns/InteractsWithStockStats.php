<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\InventoryLog;
use App\Models\Setting;
use App\Services\InventoryService;

trait InteractsWithStockStats
{
    /**
     * @return array{
     *     currentStock: float,
     *     targetStock: float,
     *     percentage: float,
     *     remainingStock: float,
     *     status: array{status: string, color: string, message: string, icon: string},
     *     estimatedDays: ?int,
     *     stockIn: float,
     *     stockOut: float
     * }
     */
    protected function getStockStats(): array
    {
        $currentStock = InventoryService::getCurrentStock();
        $targetStock = (float) Setting::get('target_stock', 1000);
        $percentage = InventoryService::getStockPercentage();
        $status = InventoryService::getStockStatus();
        $estimatedDays = InventoryService::getEstimatedDaysToTarget();

        $stockIn = (float) InventoryLog::stockIn()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('weight_kg');

        $stockOut = (float) InventoryLog::stockOut()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('weight_kg');

        return [
            'currentStock' => $currentStock,
            'targetStock' => $targetStock,
            'percentage' => $percentage,
            'remainingStock' => max($targetStock - $currentStock, 0),
            'status' => $status,
            'estimatedDays' => $estimatedDays,
            'stockIn' => $stockIn,
            'stockOut' => $stockOut,
        ];
    }
}
