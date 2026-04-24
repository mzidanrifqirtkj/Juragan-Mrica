<?php

namespace App\Filament\Resources\FarmerResource\RelationManagers;

use App\Support\Access;
use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Riwayat Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Access::can('transactions.view');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transaction_code')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_code')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Kode Transaksi')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Berat')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' kg')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_per_kg')
                    ->label('Harga/Kg')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid'    => 'success',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => $state === 'paid' ? 'Lunas' : 'Belum Bayar'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->visible(fn (): bool => ! Access::petani() && Access::can('transactions.create')),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()
                    ->visible(fn (): bool => ! Access::petani() && Access::can('transactions.edit')),
                Actions\DeleteAction::make()
                    ->visible(fn (): bool => ! Access::petani() && Access::can('transactions.delete')),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => ! Access::petani() && Access::can('transactions.delete')),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }
}
