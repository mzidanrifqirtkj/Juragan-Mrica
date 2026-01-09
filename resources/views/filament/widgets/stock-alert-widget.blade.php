<x-filament-widgets::widget>
    @if ($hasReachedTarget)
        <x-filament::section class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <x-filament::icon icon="heroicon-o-check-badge" class="w-12 h-12 text-green-500" />
                </div>
                <div class="flex-grow">
                    <h3 class="text-lg font-bold text-green-700 dark:text-green-300">
                        🎉 Target Tercapai! Stok Siap Dijual ke Pengepul
                    </h3>
                    <p class="text-green-600 dark:text-green-400">
                        Stok saat ini: <strong>{{ number_format($currentStock, 2) }} kg</strong>
                        (Target: {{ number_format($targetStock, 0) }} kg)
                    </p>
                    <p class="text-sm text-green-500 dark:text-green-500 mt-1">
                        Hubungi pengepul untuk melakukan penjualan bulk 1 ton!
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <x-filament::button :href="route('filament.admin.resources.sales.create')" tag="a" color="success" size="lg"
                        icon="heroicon-o-arrow-up-tray">
                        Jual ke Pengepul
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    @elseif($isLowStock)
        <x-filament::section class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-12 h-12 text-red-500" />
                </div>
                <div class="flex-grow">
                    <h3 class="text-lg font-bold text-red-700 dark:text-red-300">
                        ⚠️ Peringatan: Stok Gudang Rendah!
                    </h3>
                    <p class="text-red-600 dark:text-red-400">
                        Stok saat ini hanya <strong>{{ number_format($currentStock, 2) }} kg</strong>
                    </p>
                    <p class="text-sm text-red-500 dark:text-red-500 mt-1">
                        Segera terima setoran dari petani untuk menambah stok gudang.
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <x-filament::button :href="route('filament.admin.resources.transactions.create')" tag="a" color="danger" size="lg"
                        icon="heroicon-o-plus">
                        Input Setoran
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    @else
        <x-filament::section class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <x-filament::icon icon="heroicon-o-bell-alert" class="w-12 h-12 text-amber-500" />
                </div>
                <div class="flex-grow">
                    <h3 class="text-lg font-bold text-amber-700 dark:text-amber-300">
                        📢 Hampir Mencapai Target 1 Ton!
                    </h3>
                    <p class="text-amber-600 dark:text-amber-400">
                        Stok saat ini: <strong>{{ number_format($currentStock, 2) }} kg</strong>
                        (Kurang {{ number_format($targetStock - $currentStock, 2) }} kg lagi)
                    </p>
                    <p class="text-sm text-amber-500 dark:text-amber-500 mt-1">
                        Persiapkan penjualan bulk ke pengepul dalam waktu dekat.
                    </p>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
