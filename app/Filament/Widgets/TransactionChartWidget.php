<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionChartWidget extends ChartWidget
{
    protected ?string $heading = 'Trend Transaksi 7 Hari Terakhir';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $purchaseData = [];
        $salesData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');

            $purchaseData[] = Transaction::whereDate('transaction_date', $date)->sum('weight_kg');
            $salesData[] = Sale::whereDate('sale_date', $date)->sum('weight_kg');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Setoran (kg)',
                    'data' => $purchaseData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
                [
                    'label' => 'Penjualan (kg)',
                    'data' => $salesData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
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
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Berat (kg)',
                    ],
                ],
            ],
        ];
    }
}
