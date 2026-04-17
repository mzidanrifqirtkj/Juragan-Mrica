{{-- Partial: Tabel Detail Transaksi --}}
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tanggal</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Kode</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Petani</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Tujuan</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Berat</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Beli/kg</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Jual/kg</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Laba/kg</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Total Laba</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($sales as $sale)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $sale['sale_date']->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="inline-flex px-2 py-1 rounded text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">
                            {{ $sale['sale_code'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                        {{ $sale['farmer_name'] }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $badgeClass = match ($sale['sale_type']) {
                                'Gudang'
                                    => 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400',
                                'Pasar' => 'bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-400',
                                default
                                    => 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-1 rounded text-xs font-medium {{ $badgeClass }}">
                            {{ $sale['sale_type'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                        {{ number_format($sale['weight_kg'], 2) }} kg
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                        Rp {{ number_format($sale['buy_price_per_kg'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                        Rp {{ number_format($sale['sell_price_per_kg'], 0, ',', '.') }}
                    </td>
                    <td
                        class="px-4 py-3 text-right font-medium {{ $sale['profit_per_kg'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        Rp {{ number_format($sale['profit_per_kg'], 0, ',', '.') }}
                    </td>
                    <td
                        class="px-4 py-3 text-right font-bold {{ $sale['total_profit'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        Rp {{ number_format($sale['total_profit'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                        <p class="font-medium">Belum ada data</p>
                        <p class="text-sm">Tidak ada transaksi pada periode ini</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if (count($sales) > 0)
            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-700 border-t-2 border-gray-300 dark:border-gray-600">
                    <td colspan="4"
                        class="px-4 py-4 text-right font-bold text-gray-700 dark:text-gray-300 uppercase text-xs tracking-wider">
                        Total Keseluruhan
                    </td>
                    <td class="px-4 py-4 text-right font-bold text-gray-900 dark:text-white">
                        {{ number_format($summary['total_weight'], 2) }} kg
                    </td>
                    <td class="px-4 py-4 text-right font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($summary['total_buy_amount'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 text-right font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($summary['total_sell_amount'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 text-right text-gray-500 dark:text-gray-400">
                        -
                    </td>
                    <td
                        class="px-4 py-4 text-right {{ $summary['total_profit'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        <span class="text-lg font-bold">Rp
                            {{ number_format($summary['total_profit'], 0, ',', '.') }}</span>
                        <span class="block text-xs font-normal">({{ number_format($summary['profit_margin'], 1) }}%
                            margin)</span>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
