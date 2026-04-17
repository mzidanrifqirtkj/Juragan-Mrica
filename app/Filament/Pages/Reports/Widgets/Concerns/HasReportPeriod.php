<?php

namespace App\Filament\Pages\Reports\Widgets\Concerns;

use Illuminate\Support\Carbon;
use Livewire\Attributes\Reactive;

trait HasReportPeriod
{
    #[Reactive]
    public ?string $startDate = null;

    #[Reactive]
    public ?string $endDate = null;

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function getPeriod(): array
    {
        return [
            Carbon::parse($this->startDate ?? now()->startOfMonth()),
            Carbon::parse($this->endDate ?? now()),
        ];
    }
}
