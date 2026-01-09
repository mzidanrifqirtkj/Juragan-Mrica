<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\Sale;
use App\Models\Farmer;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use UnitEnum;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan & Analisis';

    protected static string|UnitEnum|null $navigationGroup = 'Gudang';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reports';

    #[Url ]
    public ?string $startDate = null;

    #[Url ]
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = $this->startDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $this->endDate ?? now()->format('Y-m-d');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Periode')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Dari Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live(),
                        DatePicker::make('endDate')
                            ->label('Sampai Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live(),
                    ])
                    ->columns(2)
                    ->compact(),
            ]);
    }

    public function getReportData(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        // Transactions (Purchases)
        $transactions = Transaction::whereBetween('transaction_date', [ $start, $end ]);
        $totalPurchaseWeight = $transactions->sum('weight_kg');
        $totalPurchaseAmount = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        $avgPurchasePrice = $totalPurchaseWeight > 0 ? $totalPurchaseAmount / $totalPurchaseWeight : 0;

        // Sales
        $sales = Sale::whereBetween('sale_date', [ $start, $end ]);
        $totalSalesWeight = $sales->sum('weight_kg');
        $totalSalesAmount = $sales->sum('total_amount');
        $totalSalesCount = $sales->count();
        $avgSalesPrice = $totalSalesWeight > 0 ? $totalSalesAmount / $totalSalesWeight : 0;

        // Sales by type
        $retailSales = Sale::retail()->whereBetween('sale_date', [ $start, $end ]);
        $bulkSales = Sale::bulk()->whereBetween('sale_date', [ $start, $end ]);

        // Profit
        $grossProfit = $totalSalesAmount - $totalPurchaseAmount;
        $profitMargin = $totalSalesAmount > 0 ? ($grossProfit / $totalSalesAmount) * 100 : 0;

        // Top Farmers
        $topFarmers = Farmer::withSum([
            'transactions' => function ($query) use ($start, $end) {
                $query->whereBetween('transaction_date', [ $start, $end ]);
            }
        ], 'weight_kg')
            ->orderByDesc('transactions_sum_weight_kg')
            ->limit(5)
            ->get();

        return [
            'period' => [
                'start' => $start->format('d M Y'),
                'end' => $end->format('d M Y'),
            ],
            'purchases' => [
                'total_weight' => $totalPurchaseWeight,
                'total_amount' => $totalPurchaseAmount,
                'count' => $totalTransactions,
                'avg_price' => $avgPurchasePrice,
            ],
            'sales' => [
                'total_weight' => $totalSalesWeight,
                'total_amount' => $totalSalesAmount,
                'count' => $totalSalesCount,
                'avg_price' => $avgSalesPrice,
                'retail' => [
                    'weight' => $retailSales->sum('weight_kg'),
                    'amount' => $retailSales->sum('total_amount'),
                    'count' => $retailSales->count(),
                ],
                'bulk' => [
                    'weight' => $bulkSales->sum('weight_kg'),
                    'amount' => $bulkSales->sum('total_amount'),
                    'count' => $bulkSales->count(),
                ],
            ],
            'profit' => [
                'gross' => $grossProfit,
                'margin' => $profitMargin,
            ],
            'top_farmers' => $topFarmers,
        ];
    }

    public function getDailyChartData(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

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
            'labels' => $labels,
            'purchases' => $purchaseData,
            'sales' => $salesData,
        ];
    }

    public function getSalesTypeChartData(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $retail = Sale::retail()->whereBetween('sale_date', [ $start, $end ])->sum('total_amount');
        $bulk = Sale::bulk()->whereBetween('sale_date', [ $start, $end ])->sum('total_amount');

        return [
            'labels' => [ 'Retail (Pasar)', 'Bulk (Pengepul)' ],
            'data' => [ $retail, $bulk ],
        ];
    }
}
