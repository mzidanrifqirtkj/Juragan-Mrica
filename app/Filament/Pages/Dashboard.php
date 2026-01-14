<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentTransactionsWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\StockAlertWidget;
use App\Filament\Widgets\StockOverviewWidget;
use App\Filament\Widgets\TransactionChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return Heroicon::OutlinedHome;
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Dashboard';
    }

    /**
     * Get dashboard widgets in optimal order
     */
    public function getWidgets(): array
    {
        return [
            StockOverviewWidget::class,       // Full width - Stock gauge + info
            StatsOverviewWidget::class,       // Stats cards row
            StockAlertWidget::class,          // Alert if needed
            TransactionChartWidget::class,    // Chart - full width
            RecentTransactionsWidget::class,  // Table - full width
        ];
    }

    /**
     * Configure responsive columns
     */
    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 2,
            'lg' => 4,
            'xl' => 4,
            '2xl' => 4,
        ];
    }
}
