<?php

namespace App\Filament\Widgets;

use App\Services\InventoryService;
use App\Models\Setting;
use Filament\Widgets\Widget;

class StockAlertWidget extends Widget
{
    protected string $view = 'filament.widgets.stock-alert-widget';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only show when stock is near target or very low
        return InventoryService::isNearTarget() ||
            InventoryService::hasReachedTarget() ||
            InventoryService::isLowStock();
    }

    public function getViewData(): array
    {
        $status = InventoryService::getStockStatus();
        $currentStock = InventoryService::getCurrentStock();
        $targetStock = Setting::get('target_stock', 1000);

        return [
            'status' => $status,
            'currentStock' => $currentStock,
            'targetStock' => $targetStock,
            'hasReachedTarget' => InventoryService::hasReachedTarget(),
            'isLowStock' => InventoryService::isLowStock(),
        ];
    }
}
