<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Services\InventoryService;
use App\Support\Access;
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
                ->visible(fn (): bool => Access::can('sales.create'))
                ->disabled(fn () => $currentStock <= 0)
                ->tooltip(fn () => $currentStock <= 0 ? 'Stok kosong, tidak bisa membuat penjualan' : null),
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
            'warehouse' => Tab::make('Gudang')
                ->icon('heroicon-o-home-modern')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sale_type', 'warehouse')),
            'market' => Tab::make('Pasar')
                ->icon('heroicon-o-building-storefront')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sale_type', 'market')),
            'retail' => Tab::make('Eceran')
                ->icon('heroicon-o-shopping-cart')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sale_type', 'retail')),
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('sale_date', today())),
        ];
    }
}
