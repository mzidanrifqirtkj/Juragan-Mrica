<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Transaction;
use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Reactive;

/**
 * Widget Chart Trend Harian
 * 
 * Menampilkan line chart perbandingan pembelian vs penjualan harian
 */
class DailyTrendChartWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 2;

    #[Reactive]
    public ?string $startDate = null;

    #[Reactive]
    public ?string $endDate = null;

    public function getHeading(): ?string
    {
        return 'Trend Pembelian vs Penjualan';
    }

    public function getDescription(): ?string
    {
        return 'Perbandingan nilai transaksi harian';
    }

    public function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getData(): array
    {
        $start = Carbon::parse($this->startDate ?? now()->startOfMonth());
        $end = Carbon::parse($this->endDate ?? now());

        $labels = [];
        $purchaseData = [];
        $salesData = [];

        $current = $start->copy();
        while ($current <= $end) {
            $labels[] = $current->format('d M');
            $purchaseData[] = Transaction::whereDate('transaction_date', $current)->sum('total_amount');
            $salesData[] = Sale::whereDate('sale_date', $current)->sum('total_amount');
            $current->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pembelian',
                    'data' => $purchaseData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Penjualan',
                    'data' => $salesData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
