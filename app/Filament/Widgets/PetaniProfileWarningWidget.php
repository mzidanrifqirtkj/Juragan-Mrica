<?php

namespace App\Filament\Widgets;

use App\Support\Access;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PetaniProfileWarningWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Access::petani() && ! Access::petaniConfigured();
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Akun Petani Belum Tertaut', 'Hubungi admin')
                ->description('Akun Anda belum dikaitkan ke profil petani. Data setoran pribadi belum bisa ditampilkan.')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
