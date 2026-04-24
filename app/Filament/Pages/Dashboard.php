<?php

namespace App\Filament\Pages;

use App\Support\Access;
use App\Filament\Widgets\RecentTransactionsWidget;
use App\Filament\Widgets\PetaniProfileWarningWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\StockAlertWidget;
use App\Filament\Widgets\StockOverviewWidget;
use App\Filament\Widgets\TransactionChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return Heroicon::OutlinedHome;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Dashboard';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Access::can('dashboard.view');
    }

    public static function canAccess(): bool
    {
        return Access::can('dashboard.view');
    }

    /**
     * Get dashboard widgets in optimal order
     */
    public function getWidgets(): array
    {
        if (Access::petani() && ! Access::petaniConfigured()) {
            return [
                PetaniProfileWarningWidget::class,
            ];
        }

        $widgets = [];

        if (Access::can('inventory.view')) {
            $widgets[] = StockOverviewWidget::class;
            $widgets[] = StockAlertWidget::class;
        }

        if (Access::can('transactions.view') || Access::can('sales.view') || Access::can('reports.view')) {
            $widgets[] = StatsOverviewWidget::class;
            $widgets[] = TransactionChartWidget::class;
            $widgets[] = RecentTransactionsWidget::class;
        }

        return $widgets;
    }

    public function getColumns(): int|array
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
