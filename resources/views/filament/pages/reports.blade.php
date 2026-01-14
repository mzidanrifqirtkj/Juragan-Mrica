<x-filament-panels::page>
    @php
        $data = $this->getReportData();
        $chartData = $this->getDailyChartData();
        $salesTypeData = $this->getSalesTypeChartData();
        $salesDetailReport = $this->getSalesDetailReport();
    @endphp

    <style>
        .stat-card {
            @apply relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm transition-all hover:shadow-md;
        }

        .stat-card::before {
            @apply absolute inset-0 h-1 w-full;
            content: '';
        }

        .stat-card.primary::before {
            @apply bg-gradient-to-r from-blue-400 to-blue-600;
        }

        .stat-card.success::before {
            @apply bg-gradient-to-r from-green-400 to-green-600;
        }

        .stat-card.danger::before {
            @apply bg-gradient-to-r from-red-400 to-red-600;
        }

        .stat-card.warning::before {
            @apply bg-gradient-to-r from-amber-400 to-amber-600;
        }

        .stat-icon {
            @apply flex h-12 w-12 items-center justify-center rounded-lg text-lg;
        }

        .table-header {
            @apply sticky top-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 font-semibold text-gray-700 dark:text-gray-300;
        }

        .table-row-hover {
            @apply hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors;
        }

        .badge {
            @apply inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium transition-all;
        }

        .chart-container {
            @apply rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm;
        }
    </style>

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Laporan & Analisis</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $data['period']['start'] }} → {{ $data['period']['end'] }}
            </p>
        </div>
    </div>

    <!-- Date Filter Form -->
    <x-filament::section class="mb-6">
        <div class="space-y-4">
            {{ $this->form }}
        </div>
    </x-filament::section>

    <!-- Summary Stats - Improved Design -->
    <div class="mb-8">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Ringkasan Kinerja</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pembelian -->
            <div class="stat-card primary">
                <div class="relative z-10">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pembelian dari Petani</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($data['purchases']['total_amount'], 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                {{ number_format($data['purchases']['total_weight'], 2) }} kg
                                <span class="mx-1">•</span>
                                {{ $data['purchases']['count'] }} transaksi
                            </p>
                        </div>
                        <div class="stat-icon bg-blue-100 dark:bg-blue-900/20">📥</div>
                    </div>
                </div>
            </div>

            <!-- Penjualan -->
            <div class="stat-card success">
                <div class="relative z-10">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Penjualan</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($data['sales']['total_amount'], 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                {{ number_format($data['sales']['total_weight'], 2) }} kg
                                <span class="mx-1">•</span>
                                {{ $data['sales']['count'] }} transaksi
                            </p>
                        </div>
                        <div class="stat-icon bg-green-100 dark:bg-green-900/20">📤</div>
                    </div>
                </div>
            </div>

            <!-- Laba -->
            <div class="stat-card {{ $data['profit']['gross'] >= 0 ? 'success' : 'danger' }}">
                <div class="relative z-10">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Laba Kotor</p>
                            <p
                                class="mt-2 text-2xl font-bold {{ $data['profit']['gross'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($data['profit']['gross'], 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                Margin: <span
                                    class="font-semibold">{{ number_format($data['profit']['margin'], 1) }}%</span>
                            </p>
                        </div>
                        <div
                            class="stat-icon {{ $data['profit']['gross'] >= 0 ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20' }}">
                            💰</div>
                    </div>
                </div>
            </div>

            <!-- Comparison -->
            <div class="stat-card warning">
                <div class="relative z-10">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Selisih Harga</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                Rp
                                {{ number_format($data['sales']['avg_price'] - $data['purchases']['avg_price'], 0, ',', '.') }}/kg
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                Beli: Rp {{ number_format($data['purchases']['avg_price'], 0, ',', '.') }}
                                <br>
                                Jual: Rp {{ number_format($data['sales']['avg_price'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="stat-icon bg-amber-100 dark:bg-amber-900/20">📊</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mb-8">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Visualisasi Data</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Daily Trend Chart -->
            <div class="lg:col-span-2">
                <div class="chart-container">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="text-xl">📈</span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Trend Pembelian vs Penjualan</h3>
                    </div>
                    <div style="height: 350px;">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Sales Type Pie Chart -->
            <div>
                <div class="chart-container">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="text-xl">🎯</span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Komposisi Penjualan</h3>
                    </div>
                    <div style="height: 350px;">
                        <canvas id="salesTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Breakdown -->
    <div class="mb-8">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Breakdown Penjualan</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Warehouse -->
            <div
                class="rounded-lg border border-green-200 dark:border-green-900/30 bg-green-50 dark:bg-green-900/10 p-4">
                <div class="mb-4 flex items-center gap-2">
                    <span class="text-2xl">🏭</span>
                    <h3 class="font-semibold text-green-900 dark:text-green-400">Gudang</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Berat</span>
                        <span
                            class="font-bold text-green-700 dark:text-green-400">{{ number_format($data['sales']['warehouse']['weight'], 2) }}
                            kg</span>
                    </div>
                    <div class="flex justify-between border-t border-green-200 dark:border-green-900/30 pt-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Nilai</span>
                        <span class="font-bold text-green-700 dark:text-green-400">Rp
                            {{ number_format($data['sales']['warehouse']['amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-green-200 dark:border-green-900/30 pt-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Transaksi</span>
                        <span
                            class="rounded-full bg-green-200 dark:bg-green-900/30 px-3 py-1 text-sm font-semibold text-green-700 dark:text-green-400">
                            {{ $data['sales']['warehouse']['count'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Market -->
            <div class="rounded-lg border border-blue-200 dark:border-blue-900/30 bg-blue-50 dark:bg-blue-900/10 p-4">
                <div class="mb-4 flex items-center gap-2">
                    <span class="text-2xl">🛒</span>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-400">Pasar</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Berat</span>
                        <span
                            class="font-bold text-blue-700 dark:text-blue-400">{{ number_format($data['sales']['market']['weight'], 2) }}
                            kg</span>
                    </div>
                    <div class="flex justify-between border-t border-blue-200 dark:border-blue-900/30 pt-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Nilai</span>
                        <span class="font-bold text-blue-700 dark:text-blue-400">Rp
                            {{ number_format($data['sales']['market']['amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-blue-200 dark:border-blue-900/30 pt-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Transaksi</span>
                        <span
                            class="rounded-full bg-blue-200 dark:bg-blue-900/30 px-3 py-1 text-sm font-semibold text-blue-700 dark:text-blue-400">
                            {{ $data['sales']['market']['count'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Retail -->
            <div
                class="rounded-lg border border-amber-200 dark:border-amber-900/30 bg-amber-50 dark:bg-amber-900/10 p-4">
                <div class="mb-4 flex items-center gap-2">
                    <span class="text-2xl">📦</span>
                    <h3 class="font-semibold text-amber-900 dark:text-amber-400">Eceran</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Berat</span>
                        <span
                            class="font-bold text-amber-700 dark:text-amber-400">{{ number_format($data['sales']['retail']['weight'], 2) }}
                            kg</span>
                    </div>
                    <div class="flex justify-between border-t border-amber-200 dark:border-amber-900/30 pt-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Nilai</span>
                        <span class="font-bold text-amber-700 dark:text-amber-400">Rp
                            {{ number_format($data['sales']['retail']['amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-amber-200 dark:border-amber-900/30 pt-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Transaksi</span>
                        <span
                            class="rounded-full bg-amber-200 dark:bg-amber-900/30 px-3 py-1 text-sm font-semibold text-amber-700 dark:text-amber-400">
                            {{ $data['sales']['retail']['count'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Sales Report -->
    <div class="mb-8">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Detail Penjualan & Perhitungan Laba</h2>
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="table-header border-b border-gray-300 dark:border-gray-700">
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Petani</th>
                        <th class="px-4 py-3 text-center">Tujuan</th>
                        <th class="px-4 py-3 text-right">Berat</th>
                        <th class="px-4 py-3 text-right">Beli/kg</th>
                        <th class="px-4 py-3 text-right">Jual/kg</th>
                        <th class="px-4 py-3 text-right">Laba/kg</th>
                        <th class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400">Total Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($salesDetailReport['sales'] as $sale)
                        <tr class="table-row-hover bg-white dark:bg-gray-800">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $sale['sale_date']->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="badge bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    {{ $sale['sale_code'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $sale['farmer_name'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="badge {{ $sale['sale_type'] === 'Gudang' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($sale['sale_type'] === 'Pasar' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400') }}">
                                    {{ $sale['sale_type'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                {{ number_format($sale['weight_kg'], 2) }} kg
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                Rp {{ number_format($sale['buy_price_per_kg'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                Rp {{ number_format($sale['sell_price_per_kg'], 0, ',', '.') }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-semibold {{ $sale['profit_per_kg'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($sale['profit_per_kg'], 0, ',', '.') }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-bold {{ $sale['total_profit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($sale['total_profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <p class="text-lg">📭</p>
                                <p class="mt-2">Belum ada data penjualan pada periode ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-header border-t-2 border-gray-300 dark:border-gray-700 font-bold">
                        <td colspan="4" class="px-4 py-4 text-right">TOTAL:</td>
                        <td class="px-4 py-4 text-right">
                            {{ number_format($salesDetailReport['summary']['total_weight'], 2) }} kg
                        </td>
                        <td colspan="2" class="px-4 py-4 text-right">
                            Rp {{ number_format($salesDetailReport['summary']['total_buy_amount'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-right">
                            Rp {{ number_format($salesDetailReport['summary']['total_sell_amount'], 0, ',', '.') }}
                        </td>
                        <td
                            class="px-4 py-4 text-right {{ $salesDetailReport['summary']['total_profit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            <p>Rp {{ number_format($salesDetailReport['summary']['total_profit'], 0, ',', '.') }}</p>
                            <p class="text-xs font-normal">
                                (Margin: {{ number_format($salesDetailReport['summary']['profit_margin'], 1) }}%)
                            </p>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Top Farmers -->
    <div>
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Top Petani</h2>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="table-header border-b border-gray-300 dark:border-gray-700">
                        <th class="px-6 py-4 text-left">#</th>
                        <th class="px-6 py-4 text-left">Kode</th>
                        <th class="px-6 py-4 text-left">Nama Petani</th>
                        <th class="px-6 py-4 text-right">Total Kg</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($data['top_farmers'] as $index => $farmer)
                        <tr class="table-row-hover bg-white dark:bg-gray-800">
                            <td class="px-6 py-4 text-center">
                                <span class="text-2xl">
                                    @if ($index === 0)
                                        🥇
                                    @elseif($index === 1)
                                        🥈
                                    @elseif($index === 2)
                                        🥉
                                    @else
                                        <span
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-sm font-bold text-gray-700 dark:text-gray-300">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ $farmer->farmer_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $farmer->name }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    {{ number_format($farmer->transactions_sum_weight_kg ?? 0, 2) }} kg
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <p class="text-lg">📭</p>
                                <p class="mt-2">Belum ada data transaksi pada periode ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Daily Chart with improved styling
                const dailyCtx = document.getElementById('dailyChart').getContext('2d');
                new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: [{
                                label: 'Pembelian (Rp)',
                                data: @json($chartData['purchases']),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.08)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                pointBorderColor: '#fff',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Penjualan (Rp)',
                                data: @json($chartData['sales']),
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointBackgroundColor: 'rgb(16, 185, 129)',
                                pointBorderColor: '#fff',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    usePointStyle: true,
                                }
                            },
                            filler: {
                                propagate: true,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + (value / 1000000 >= 1 ? (value / 1000000).toFixed(
                                            1) + 'M' : (value / 1000).toFixed(0) + 'K');
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                }
                            }
                        }
                    }
                });

                // Sales Type Doughnut Chart with improved styling
                const pieCtx = document.getElementById('salesTypeChart').getContext('2d');
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($salesTypeData['labels']),
                        datasets: [{
                            data: @json($salesTypeData['data']),
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                            ],
                            borderColor: [
                                'rgb(16, 185, 129)',
                                'rgb(59, 130, 246)',
                                'rgb(245, 158, 11)',
                            ],
                            borderWidth: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    usePointStyle: true,
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
