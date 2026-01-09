<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Services\InventoryService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        $currentStock = InventoryService::getCurrentStock();

        return [
            Actions\CreateAction::make()
                ->label('Buat Penjualan')
                ->disabled(fn() => $currentStock <= 0)
                ->tooltip(fn() => $currentStock <= 0 ? 'Stok kosong, tidak bisa membuat penjualan' : null),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SaleResource\Widgets\SaleStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-rectangle-stack'),
            'retail' => Tab::make('Retail (Pasar)')
                ->icon('heroicon-o-shopping-cart')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('sale_type', 'retail')),
            'bulk' => Tab::make('Bulk (Pengepul)')
                ->icon('heroicon-o-cube')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('sale_type', 'bulk')),
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('sale_date', today())),
        ];
    }
}
