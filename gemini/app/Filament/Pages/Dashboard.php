<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            UpcomingInvoicesWidget::class,
        ];
    }
}