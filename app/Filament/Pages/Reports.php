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

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

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
                            ->label('Dari Tanggal Petani Menjual')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live(),
                        DatePicker::make('endDate')
                            ->label('Sampai Tanggal Setor ke Gudang')
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
        $warehouseSales = Sale::where('sale_type', 'warehouse')->whereBetween('sale_date', [ $start, $end ]);
        $marketSales = Sale::where('sale_type', 'market')->whereBetween('sale_date', [ $start, $end ]);
        $retailSales = Sale::where('sale_type', 'retail')->whereBetween('sale_date', [ $start, $end ]);

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
                'warehouse' => [
                    'weight' => $warehouseSales->sum('weight_kg'),
                    'amount' => $warehouseSales->sum('total_amount'),
                    'count' => $warehouseSales->count(),
                ],
                'market' => [
                    'weight' => $marketSales->sum('weight_kg'),
                    'amount' => $marketSales->sum('total_amount'),
                    'count' => $marketSales->count(),
                ],
                'retail' => [
                    'weight' => $retailSales->sum('weight_kg'),
                    'amount' => $retailSales->sum('total_amount'),
                    'count' => $retailSales->count(),
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

        $warehouse = Sale::where('sale_type', 'warehouse')->whereBetween('sale_date', [ $start, $end ])->sum('total_amount');
        $market = Sale::where('sale_type', 'market')->whereBetween('sale_date', [ $start, $end ])->sum('total_amount');
        $retail = Sale::where('sale_type', 'retail')->whereBetween('sale_date', [ $start, $end ])->sum('total_amount');

        return [
            'labels' => [ 'Gudang', 'Pasar', 'Eceran' ],
            'data' => [ $warehouse, $market, $retail ],
        ];
    }

    /**
     * Get detailed sales report with profit calculation
     */
    public function getSalesDetailReport(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $sales = Sale::with('transaction.farmer')
            ->whereBetween('sale_date', [ $start, $end ])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                $buyPrice = $sale->transaction ? $sale->transaction->price_per_kg : 0;
                $sellPrice = $sale->price_per_kg;
                $profitPerKg = $sellPrice - $buyPrice;
                $totalProfit = $profitPerKg * $sale->weight_kg;

                return [
                    'sale_code' => $sale->sale_code,
                    'farmer_name' => $sale->transaction?->farmer?->name ?? '-',
                    'transaction_code' => $sale->transaction?->transaction_code ?? '-',
                    'sale_type' => match ($sale->sale_type) {
                        'warehouse' => 'Gudang',
                        'market'    => 'Pasar',
                        'retail'    => 'Eceran',
                        default     => $sale->sale_type,
                    },
                    'buyer_name' => $sale->buyer_name ?? '-',
                    'weight_kg' => $sale->weight_kg,
                    'buy_price_per_kg' => $buyPrice,
                    'sell_price_per_kg' => $sellPrice,
                    'profit_per_kg' => $profitPerKg,
                    'total_buy_amount' => $buyPrice * $sale->weight_kg,
                    'total_sell_amount' => $sale->total_amount,
                    'total_profit' => $totalProfit,
                    'sale_date' => $sale->sale_date,
                ];
            });

        // Summary
        $totalBuyAmount = $sales->sum('total_buy_amount');
        $totalSellAmount = $sales->sum('total_sell_amount');
        $totalProfit = $sales->sum('total_profit');
        $totalWeight = $sales->sum('weight_kg');

        return [
            'sales' => $sales,
            'summary' => [
                'total_weight' => $totalWeight,
                'total_buy_amount' => $totalBuyAmount,
                'total_sell_amount' => $totalSellAmount,
                'total_profit' => $totalProfit,
                'profit_margin' => $totalSellAmount > 0 ? ($totalProfit / $totalSellAmount) * 100 : 0,
            ],
        ];
    }
}
