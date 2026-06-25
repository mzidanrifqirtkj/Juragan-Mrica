<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Transaction;
use App\Support\Access;
use Filament\Widgets\ChartWidget;

class TransactionChartWidget extends ChartWidget
{
    protected ?string $heading = 'Trend Transaksi 7 Hari Terakhir';

    protected ?string $description = 'Perbandingan setoran dan penjualan dalam kg';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return Access::can('transactions.view') && Access::petaniConfigured();
    }

    public function getHeading(): ?string
    {
        return Access::petani() ? 'Trend Setoran Saya 7 Hari Terakhir' : $this->heading;
    }

    public function getDescription(): ?string
    {
        return Access::petani() ? 'Perkembangan berat setoran pribadi Anda' : $this->description;
    }

    protected function getData(): array
    {
        $labels = [];
        $purchaseData = [];
        $salesData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->translatedFormat('d M');

            $purchaseData[] = Access::restrictPetaniTransactionQuery(Transaction::query())
                ->whereDate('transaction_date', $date)
                ->sum('weight_kg');

            if (! Access::petani()) {
                $salesData[] = Sale::whereDate('sale_date', $date)->sum('weight_kg');
            }
        }

        $datasets = [
            [
                'label' => 'Setoran (kg)',
                'data' => $purchaseData,
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'borderColor' => 'rgb(59, 130, 246)',
                'fill' => true,
                'tension' => 0.4,
                'pointBackgroundColor' => 'rgb(59, 130, 246)',
                'pointBorderColor' => '#fff',
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
            ],
        ];

        if (! Access::petani()) {
            $datasets[] = [
                'label' => 'Penjualan (kg)',
                'data' => $salesData,
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'borderColor' => 'rgb(16, 185, 129)',
                'fill' => true,
                'tension' => 0.4,
                'pointBackgroundColor' => 'rgb(16, 185, 129)',
                'pointBorderColor' => '#fff',
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
            ];
        }

        return [
            'datasets' => $datasets,
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
                    'labels' => [
                        'padding' => 15,
                        'usePointStyle' => true,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Berat (kg)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'borderWidth' => 2,
                ],
            ],
        ];
    }
}
