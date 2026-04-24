<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Services\InventoryService;
use App\Support\Access;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Input Setoran')
                ->visible(fn (): bool => Access::can('transactions.create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionResource\Widgets\TransactionStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-rectangle-stack'),
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('transaction_date', today())),
            'minggu_ini' => Tab::make('Minggu Ini')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()])),
            'bulan_ini' => Tab::make('Bulan Ini')
                ->icon('heroicon-o-calendar-date-range')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)),
        ];
    }
}
