<x-filament-panels::page>
    @php
        $data = $this->getReportData();
        $salesDetail = $this->getSalesDetailReport();
    @endphp

    {{-- Filter Form --}}
    <x-filament::section class="mb-6" compact>
        <x-slot name="heading">
            Filter Periode
        </x-slot>
        {{ $this->form }}
    </x-filament::section>

    {{-- Stats Widgets --}}
    @if (count($this->getHeaderWidgets()) > 0)
        <div class="mb-6">
            <x-filament-widgets::widgets :widgets="$this->getVisibleHeaderWidgets()" :columns="$this->getHeaderWidgetsColumns()" :data="['startDate' => $this->startDate, 'endDate' => $this->endDate]" />
        </div>
    @endif

    {{-- Profit Summary Card --}}
    <div
        class="mb-6 rounded-xl p-6 {{ $data['profit']['gross'] >= 0 ? 'bg-success-50 dark:bg-success-950 border border-success-200 dark:border-success-800' : 'bg-danger-50 dark:bg-danger-950 border border-danger-200 dark:border-danger-800' }}">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Laba Kotor Periode Ini</p>
                <p
                    class="text-3xl font-bold {{ $data['profit']['gross'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                    Rp {{ number_format(abs($data['profit']['gross']), 0, ',', '.') }}
                </p>
            </div>
            <div class="flex gap-4">
                <div class="text-center px-4 py-2 rounded-lg bg-white/50 dark:bg-gray-800/50">
                    <p class="text-xs text-gray-500">Margin</p>
                    <p
                        class="text-xl font-bold {{ $data['profit']['margin'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ number_format($data['profit']['margin'], 1) }}%</p>
                </div>
                <div class="text-center px-4 py-2 rounded-lg bg-white/50 dark:bg-gray-800/50">
                    <p class="text-xs text-gray-500">Selisih/kg</p>
                    <p class="text-xl font-bold text-warning-600">Rp
                        {{ number_format($data['sales']['avg_price'] - $data['purchases']['avg_price'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Cards --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Gudang --}}
        <x-filament::section compact>
            <x-slot name="heading">
                Gudang
            </x-slot>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Berat:</span>
                    <span class="font-semibold">{{ number_format($data['sales']['warehouse']['weight'], 2) }} kg</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nilai:</span>
                    <span class="font-bold text-success-600">Rp
                        {{ number_format($data['sales']['warehouse']['amount'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Transaksi:</span>
                    <x-filament::badge color="success"
                        size="sm">{{ $data['sales']['warehouse']['count'] }}</x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        {{-- Pasar --}}
        <x-filament::section compact>
            <x-slot name="heading">
                Pasar
            </x-slot>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Berat:</span>
                    <span class="font-semibold">{{ number_format($data['sales']['market']['weight'], 2) }} kg</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nilai:</span>
                    <span class="font-bold text-info-600">Rp
                        {{ number_format($data['sales']['market']['amount'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Transaksi:</span>
                    <x-filament::badge color="info"
                        size="sm">{{ $data['sales']['market']['count'] }}</x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        {{-- Eceran --}}
        <x-filament::section compact>
            <x-slot name="heading">
                Eceran
            </x-slot>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Berat:</span>
                    <span class="font-semibold">{{ number_format($data['sales']['retail']['weight'], 2) }} kg</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nilai:</span>
                    <span class="font-bold text-warning-600">Rp
                        {{ number_format($data['sales']['retail']['amount'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Transaksi:</span>
                    <x-filament::badge color="warning"
                        size="sm">{{ $data['sales']['retail']['count'] }}</x-filament::badge>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Detail Table --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">
            Detail Transaksi & Perhitungan Laba
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <th class="px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tanggal</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Kode</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Petani</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Tujuan</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Berat</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Beli/kg</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Jual/kg</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Total Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($salesDetail['sales'] as $sale)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-400">
                                {{ $sale['sale_date']->format('d M Y') }}</td>
                            <td class="px-3 py-3">
                                <x-filament::badge color="success"
                                    size="sm">{{ $sale['sale_code'] }}</x-filament::badge>
                            </td>
                            <td class="px-3 py-3 font-medium text-gray-900 dark:text-white">{{ $sale['farmer_name'] }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                @php
                                    $color = match ($sale['sale_type']) {
                                        'Gudang' => 'success',
                                        'Pasar' => 'info',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-filament::badge :color="$color"
                                    size="sm">{{ $sale['sale_type'] }}</x-filament::badge>
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600 dark:text-gray-400">
                                {{ number_format($sale['weight_kg'], 2) }} kg</td>
                            <td class="px-3 py-3 text-right text-gray-600 dark:text-gray-400">Rp
                                {{ number_format($sale['buy_price_per_kg'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-gray-600 dark:text-gray-400">Rp
                                {{ number_format($sale['sell_price_per_kg'], 0, ',', '.') }}</td>
                            <td
                                class="px-3 py-3 text-right font-bold {{ $sale['total_profit'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                Rp {{ number_format($sale['total_profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if (count($salesDetail['sales']) > 0)
                    <tfoot>
                        <tr
                            class="bg-gray-100 dark:bg-gray-700 border-t-2 border-gray-300 dark:border-gray-600 font-bold">
                            <td colspan="4" class="px-3 py-3 text-right">TOTAL</td>
                            <td class="px-3 py-3 text-right">
                                {{ number_format($salesDetail['summary']['total_weight'], 2) }} kg</td>
                            <td class="px-3 py-3 text-right">-</td>
                            <td class="px-3 py-3 text-right">-</td>
                            <td
                                class="px-3 py-3 text-right {{ $salesDetail['summary']['total_profit'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                Rp {{ number_format($salesDetail['summary']['total_profit'], 0, ',', '.') }}
                                <span
                                    class="block text-xs font-normal">({{ number_format($salesDetail['summary']['profit_margin'], 1) }}%)</span>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>

    {{-- Top Farmers --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">
            Top 5 Petani
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300 w-16">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nama</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Total Setoran
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($data['top_farmers'] as $index => $farmer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-center font-bold">
                                @if ($index === 0)
                                    <span class="text-yellow-500">1</span>
                                @elseif($index === 1)
                                    <span class="text-gray-400">2</span>
                                @elseif($index === 2)
                                    <span class="text-amber-600">3</span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-filament::badge color="info"
                                    size="sm">{{ $farmer->farmer_code }}</x-filament::badge>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $farmer->name }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold text-success-600 dark:text-success-400">
                                    {{ number_format($farmer->transactions_sum_weight_kg ?? 0, 2) }} kg
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Footer Widgets (Charts) --}}
    @if (count($this->getFooterWidgets()) > 0)
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Visualisasi Data</h3>
            <x-filament-widgets::widgets :widgets="$this->getVisibleFooterWidgets()" :columns="$this->getFooterWidgetsColumns()" :data="['startDate' => $this->startDate, 'endDate' => $this->endDate]" />
        </div>
    @endif
</x-filament-panels::page>
