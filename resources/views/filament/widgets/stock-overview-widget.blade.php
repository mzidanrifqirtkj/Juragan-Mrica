<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <!-- Stock Gauge -->
            <div class="flex-shrink-0 text-center">
                <div class="relative w-40 h-40">
                    <svg class="w-full h-full" viewBox="0 0 36 36">
                        <!-- Background circle -->
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="#e5e7eb" stroke-width="3" />
                        <!-- Progress circle -->
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none"
                            stroke="{{ match ($status['color']) {
                                'success' => '#10b981',
                                'warning' => '#f59e0b',
                                'danger' => '#ef4444',
                                default => '#3b82f6',
                            } }}"
                            stroke-width="3" stroke-dasharray="{{ $percentage }}, 100" stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($percentage, 0) }}%
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">ke 1 ton</span>
                    </div>
                </div>
            </div>

            <!-- Stock Info -->
            <div class="flex-grow space-y-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-filament::icon :icon="$status['icon']" class="w-5 h-5"
                            style="color: {{ match ($status['color']) {
                                'success' => '#10b981',
                                'warning' => '#f59e0b',
                                'danger' => '#ef4444',
                                default => '#3b82f6',
                            } }}" />
                        Stok Gudang
                    </h3>
                    <p class="text-3xl font-bold"
                        style="color: {{ match ($status['color']) {
                            'success' => '#10b981',
                            'warning' => '#f59e0b',
                            'danger' => '#ef4444',
                            default => '#3b82f6',
                        } }}">
                        {{ number_format($currentStock, 2) }} kg
                    </p>
                </div>

                <div class="flex gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Target:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($targetStock, 0) }}
                            kg</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Sisa:</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ number_format($targetStock - $currentStock, 2) }}
                            kg</span>
                    </div>
                    @if ($estimatedDays !== null && $estimatedDays > 0)
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Estimasi:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $estimatedDays }} hari</span>
                        </div>
                    @endif
                </div>

                <div class="p-3 rounded-lg"
                    style="background-color: {{ match ($status['color']) {
                        'success' => 'rgba(16, 185, 129, 0.1)',
                        'warning' => 'rgba(245, 158, 11, 0.1)',
                        'danger' => 'rgba(239, 68, 68, 0.1)',
                        default => 'rgba(59, 130, 246, 0.1)',
                    } }}">
                    <p class="text-sm font-medium"
                        style="color: {{ match ($status['color']) {
                            'success' => '#059669',
                            'warning' => '#d97706',
                            'danger' => '#dc2626',
                            default => '#2563eb',
                        } }}">
                        {{ $status['message'] }}
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex-shrink-0 space-y-2">
                <x-filament::button :href="route('filament.admin.resources.transactions.create')" tag="a" color="primary" icon="heroicon-o-plus"
                    class="w-full">
                    Input Setoran
                </x-filament::button>

                @if ($currentStock > 0)
                    <x-filament::button :href="route('filament.admin.resources.sales.create')" tag="a" color="success" icon="heroicon-o-arrow-up-tray"
                        class="w-full">
                        Buat Penjualan
                    </x-filament::button>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
