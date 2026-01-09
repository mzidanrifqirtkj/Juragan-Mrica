<x-filament-panels::page>
    @php
        $data = $this->getReportData();
        $chartData = $this->getDailyChartData();
        $salesTypeData = $this->getSalesTypeChartData();
    @endphp

    <!-- Date Filter Form -->
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Pembelian -->
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Pembelian dari Petani</div>
                <div class="text-2xl font-bold text-primary-600">
                    Rp {{ number_format($data['purchases']['total_amount'], 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ number_format($data['purchases']['total_weight'], 2) }} kg
                    ({{ $data['purchases']['count'] }} transaksi)
                </div>
            </div>
        </x-filament::section>

        <!-- Total Penjualan -->
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Penjualan</div>
                <div class="text-2xl font-bold text-success-600">
                    Rp {{ number_format($data['sales']['total_amount'], 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ number_format($data['sales']['total_weight'], 2) }} kg
                    ({{ $data['sales']['count'] }} transaksi)
                </div>
            </div>
        </x-filament::section>

        <!-- Laba Kotor -->
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Laba Kotor</div>
                <div
                    class="text-2xl font-bold {{ $data['profit']['gross'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    Rp {{ number_format($data['profit']['gross'], 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500">
                    Margin: {{ number_format($data['profit']['margin'], 1) }}%
                </div>
            </div>
        </x-filament::section>

        <!-- Avg Prices -->
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Harga</div>
                <div class="text-sm">
                    <span class="font-medium">Beli:</span>
                    Rp {{ number_format($data['purchases']['avg_price'], 0, ',', '.') }}/kg
                </div>
                <div class="text-sm">
                    <span class="font-medium">Jual:</span>
                    Rp {{ number_format($data['sales']['avg_price'], 0, ',', '.') }}/kg
                </div>
                <div
                    class="text-sm {{ $data['sales']['avg_price'] > $data['purchases']['avg_price'] ? 'text-success-600' : 'text-danger-600' }}">
                    Selisih: Rp
                    {{ number_format($data['sales']['avg_price'] - $data['purchases']['avg_price'], 0, ',', '.') }}/kg
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Sales Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Retail Sales -->
        <x-filament::section>
            <x-slot name="heading">
                🛒 Penjualan Retail (Pasar)
            </x-slot>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-sm text-gray-500">Berat</div>
                    <div class="font-bold text-warning-600">
                        {{ number_format($data['sales']['retail']['weight'], 2) }} kg
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Nilai</div>
                    <div class="font-bold text-warning-600">
                        Rp {{ number_format($data['sales']['retail']['amount'], 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Transaksi</div>
                    <div class="font-bold text-warning-600">
                        {{ $data['sales']['retail']['count'] }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Bulk Sales -->
        <x-filament::section>
            <x-slot name="heading">
                📦 Penjualan Bulk (Pengepul)
            </x-slot>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-sm text-gray-500">Berat</div>
                    <div class="font-bold text-success-600">
                        {{ number_format($data['sales']['bulk']['weight'], 2) }} kg
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Nilai</div>
                    <div class="font-bold text-success-600">
                        Rp {{ number_format($data['sales']['bulk']['amount'], 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Transaksi</div>
                    <div class="font-bold text-success-600">
                        {{ $data['sales']['bulk']['count'] }}
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Daily Trend Chart -->
        <div class="lg:col-span-2">
            <x-filament::section>
                <x-slot name="heading">
                    📈 Trend Pembelian vs Penjualan
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="dailyChart"></canvas>
                </div>
            </x-filament::section>
        </div>

        <!-- Sales Type Pie Chart -->
        <div>
            <x-filament::section>
                <x-slot name="heading">
                    📊 Komposisi Penjualan
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="salesTypeChart"></canvas>
                </div>
            </x-filament::section>
        </div>
    </div>

    <!-- Top Farmers -->
    <x-filament::section>
        <x-slot name="heading">
            🏆 Top 5 Petani (by Total Kg)
        </x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="text-left py-2 px-4">#</th>
                        <th class="text-left py-2 px-4">Kode</th>
                        <th class="text-left py-2 px-4">Nama Petani</th>
                        <th class="text-right py-2 px-4">Total Kg</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['top_farmers'] as $index => $farmer)
                        <tr class="border-b dark:border-gray-700">
                            <td class="py-2 px-4">
                                @if ($index === 0)
                                    🥇
                                @elseif($index === 1)
                                    🥈
                                @elseif($index === 2)
                                    🥉
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td class="py-2 px-4">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                    {{ $farmer->farmer_code }}
                                </span>
                            </td>
                            <td class="py-2 px-4 font-medium">{{ $farmer->name }}</td>
                            <td class="py-2 px-4 text-right font-bold">
                                {{ number_format($farmer->transactions_sum_weight_kg ?? 0, 2) }} kg
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 px-4 text-center text-gray-500">
                                Belum ada data transaksi pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Daily Chart
                const dailyCtx = document.getElementById('dailyChart').getContext('2d');
                new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: [{
                                label: 'Pembelian (Rp)',
                                data: @json($chartData['purchases']),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.3,
                            },
                            {
                                label: 'Penjualan (Rp)',
                                data: @json($chartData['sales']),
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.3,
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
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });

                // Sales Type Pie Chart
                const pieCtx = document.getElementById('salesTypeChart').getContext('2d');
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($salesTypeData['labels']),
                        datasets: [{
                            data: @json($salesTypeData['data']),
                            backgroundColor: [
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                            ],
                            borderColor: [
                                'rgb(245, 158, 11)',
                                'rgb(16, 185, 129)',
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
