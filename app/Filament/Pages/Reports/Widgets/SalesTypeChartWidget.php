<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Reactive;

/**
 * Widget Chart Komposisi Penjualan
 *
 * Menampilkan doughnut chart distribusi penjualan per tipe
 */
class SalesTypeChartWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    #[Reactive]
    public ?string $startDate = null;

    #[Reactive]
    public ?string $endDate = null;

    public function getHeading(): ?string
    {
        return 'Komposisi Penjualan';
    }

    public function getDescription(): ?string
    {
        return 'Distribusi per tipe penjualan';
    }

    public function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getData(): array
    {
        $start = Carbon::parse($this->startDate ?? now()->startOfMonth());
        $end = Carbon::parse($this->endDate ?? now());

        $warehouse = Sale::where('sale_type', 'warehouse')
            ->whereBetween('sale_date', [$start, $end])
            ->sum('total_amount');

        $market = Sale::where('sale_type', 'market')
            ->whereBetween('sale_date', [$start, $end])
            ->sum('total_amount');

        $retail = Sale::where('sale_type', 'retail')
            ->whereBetween('sale_date', [$start, $end])
            ->sum('total_amount');

        return [
            'datasets' => [
                [
                    'data' => [$warehouse, $market, $retail],
                    'backgroundColor' => ['#10b981', '#3b82f6', '#f59e0b'],
                    'borderWidth' => 0,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => ['Gudang', 'Pasar', 'Eceran'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
