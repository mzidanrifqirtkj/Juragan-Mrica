<?php

namespace App\Filament\Pages;

use App\Models\Farmer;
use App\Models\Sale;
use App\Models\Transaction;
use App\Support\Access;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * Halaman Laporan & Analisis
 * Menggunakan pendekatan Filament v4 modern
 */
class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    // =========================================================================
    // KONFIGURASI
    // =========================================================================

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan & Analisis';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports';

    // =========================================================================
    // STATE
    // =========================================================================

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    public ?string $quickPeriod = null;

    // =========================================================================
    // LIFECYCLE
    // =========================================================================

    public function mount(): void
    {
        $this->startDate = $this->startDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $this->endDate ?? now()->format('Y-m-d');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ! Access::petani() && Access::can('reports.view');
    }

    public static function canAccess(): bool
    {
        return ! Access::petani() && Access::can('reports.view');
    }

    // =========================================================================
    // HEADING
    // =========================================================================

    public function getSubheading(): ?string
    {
        $data = $this->getReportData();

        return "Periode: {$data['period']['start']} - {$data['period']['end']}";
    }

    // =========================================================================
    // HEADER ACTIONS
    // =========================================================================

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->dispatch('$refresh')),
        ];
    }

    // =========================================================================
    // WIDGETS
    // =========================================================================

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Pages\Reports\Widgets\ReportStatsWidget::class,
            \App\Filament\Pages\Reports\Widgets\ReportInsightStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Pages\Reports\Widgets\SalesChannelsTableWidget::class,
            \App\Filament\Pages\Reports\Widgets\SalesDetailTableWidget::class,
            \App\Filament\Pages\Reports\Widgets\TopFarmersTableWidget::class,
            \App\Filament\Pages\Reports\Widgets\DailyTrendChartWidget::class,
            \App\Filament\Pages\Reports\Widgets\SalesTypeChartWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 3,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];
    }

    // =========================================================================
    // FORM (FILTER)
    // =========================================================================

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)->schema([
                DatePicker::make('startDate')
                    ->label('Dari Tanggal')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->quickPeriod = null;
                        $this->dispatch('$refresh');
                    }),

                DatePicker::make('endDate')
                    ->label('Sampai Tanggal')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(function () {
                        $this->quickPeriod = null;
                        $this->dispatch('$refresh');
                    }),

                Select::make('quickPeriod')
                    ->label('Periode Cepat')
                    ->options([
                        'today' => 'Hari Ini',
                        'yesterday' => 'Kemarin',
                        'this_week' => 'Minggu Ini',
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                    ])
                    ->placeholder('Pilih...')
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $this->applyQuickPeriod($state, $set);
                        $this->dispatch('$refresh');
                    }),
            ]),
        ]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    protected function applyQuickPeriod(?string $period, ?Set $set = null): void
    {
        if (! $period) {
            $this->quickPeriod = null;

            return;
        }

        [$start, $end] = match ($period) {
            'today' => [now()->startOfDay(), now()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()],
            'this_month' => [now()->startOfMonth(), now()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default => [now()->startOfMonth(), now()],
        };

        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');
        $this->quickPeriod = $period;

        if ($set) {
            $set('startDate', $this->startDate);
            $set('endDate', $this->endDate);
        }
    }

    // =========================================================================
    // DATA METHODS
    // =========================================================================

    public function getReportData(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        // Purchases
        $purchases = Transaction::whereBetween('transaction_date', [$start, $end]);
        $totalPurchaseWeight = (clone $purchases)->sum('weight_kg');
        $totalPurchaseAmount = (clone $purchases)->sum('total_amount');
        $totalTransactions = (clone $purchases)->count();
        $avgPurchasePrice = $totalPurchaseWeight > 0 ? $totalPurchaseAmount / $totalPurchaseWeight : 0;

        // Sales
        $sales = Sale::whereBetween('sale_date', [$start, $end]);
        $totalSalesWeight = (clone $sales)->sum('weight_kg');
        $totalSalesAmount = (clone $sales)->sum('total_amount');
        $totalSalesCount = (clone $sales)->count();
        $avgSalesPrice = $totalSalesWeight > 0 ? $totalSalesAmount / $totalSalesWeight : 0;

        // Sales by type
        $warehouseSales = Sale::where('sale_type', 'warehouse')->whereBetween('sale_date', [$start, $end]);
        $marketSales = Sale::where('sale_type', 'market')->whereBetween('sale_date', [$start, $end]);
        $retailSales = Sale::where('sale_type', 'retail')->whereBetween('sale_date', [$start, $end]);

        // Profit
        $grossProfit = $totalSalesAmount - $totalPurchaseAmount;
        $profitMargin = $totalSalesAmount > 0 ? ($grossProfit / $totalSalesAmount) * 100 : 0;

        // Top Farmers
        $topFarmers = Farmer::withSum([
            'transactions' => fn ($query) => $query->whereBetween('transaction_date', [$start, $end]),
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
                    'weight' => (clone $warehouseSales)->sum('weight_kg'),
                    'amount' => (clone $warehouseSales)->sum('total_amount'),
                    'count' => (clone $warehouseSales)->count(),
                ],
                'market' => [
                    'weight' => (clone $marketSales)->sum('weight_kg'),
                    'amount' => (clone $marketSales)->sum('total_amount'),
                    'count' => (clone $marketSales)->count(),
                ],
                'retail' => [
                    'weight' => (clone $retailSales)->sum('weight_kg'),
                    'amount' => (clone $retailSales)->sum('total_amount'),
                    'count' => (clone $retailSales)->count(),
                ],
            ],
            'profit' => [
                'gross' => $grossProfit,
                'margin' => $profitMargin,
            ],
            'top_farmers' => $topFarmers,
        ];
    }

    public function getSalesDetailReport(): array
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $sales = Sale::with('transaction.farmer')
            ->whereBetween('sale_date', [$start, $end])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                $buyPrice = $sale->transaction?->price_per_kg ?? 0;
                $sellPrice = $sale->price_per_kg;
                $profitPerKg = $sellPrice - $buyPrice;
                $totalProfit = $profitPerKg * $sale->weight_kg;

                return [
                    'sale_code' => $sale->sale_code,
                    'farmer_name' => $sale->transaction?->farmer?->name ?? '-',
                    'sale_type' => match ($sale->sale_type) {
                        'warehouse' => 'Gudang',
                        'market' => 'Pasar',
                        'retail' => 'Eceran',
                        default => $sale->sale_type,
                    },
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

        return [
            'sales' => $sales,
            'summary' => [
                'total_weight' => $sales->sum('weight_kg'),
                'total_buy_amount' => $sales->sum('total_buy_amount'),
                'total_sell_amount' => $sales->sum('total_sell_amount'),
                'total_profit' => $sales->sum('total_profit'),
                'profit_margin' => $sales->sum('total_sell_amount') > 0
                    ? ($sales->sum('total_profit') / $sales->sum('total_sell_amount')) * 100
                    : 0,
            ],
        ];
    }
}
