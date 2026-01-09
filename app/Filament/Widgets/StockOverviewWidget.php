<?php

namespace App\Filament\Widgets;

use App\Services\InventoryService;
use App\Models\Setting;
use Filament\Widgets\Widget;

class StockOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.stock-overview-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $currentStock = InventoryService::getCurrentStock();
        $targetStock = Setting::get('target_stock', 1000);
        $percentage = InventoryService::getStockPercentage();
        $status = InventoryService::getStockStatus();
        $estimatedDays = InventoryService::getEstimatedDaysToTarget();

        return [
            'currentStock' => $currentStock,
            'targetStock' => $targetStock,
            'percentage' => $percentage,
            'status' => $status,
            'estimatedDays' => $estimatedDays,
        ];
    }
}
