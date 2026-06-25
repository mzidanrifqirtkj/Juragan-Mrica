<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Support\Access;
use App\Support\RolePermissionMatrix;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Role & Fitur';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Role & Fitur';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $sections = [
            Section::make('Informasi Role')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Role')
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ];

        foreach (RolePermissionMatrix::groupedOptions() as $feature => $group) {
            $sections[] = Section::make($group['label'])
                ->description('Atur menu dan aksi yang tersedia untuk role ini.')
                ->schema([
                    CheckboxList::make("permission_groups.{$feature}")
                        ->label('Hak akses')
                        ->options($group['options'])
                        ->columns(2),
                ]);
        }

        return $schema->components($sections);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('permissions')->whereIn('name', array_keys(RolePermissionMatrix::defaults())))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Role')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Jumlah Permission')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Access::owner();
    }

    public static function canViewAny(): bool
    {
        return Access::owner();
    }

    public static function canView(Model $record): bool
    {
        return Access::owner();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return Access::owner();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
