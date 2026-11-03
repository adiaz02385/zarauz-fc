<?php

namespace App\Filament\Pages;

use App\Services\TreasuryStatement;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected static ?string $title = 'Estado de cuenta';

    public string $period = '';

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    public function updatedPeriod(): void
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->period)) {
            $this->period = now()->format('Y-m');
        }
    }

    public function getStatement(): array
    {
        return app(TreasuryStatement::class)->forPeriod($this->period);
    }
}
