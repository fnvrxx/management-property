<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RevenueProjectionWidget;
use App\Filament\Widgets\UpcomingInvoices;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            RevenueProjectionWidget::class,
            UpcomingInvoices::class,
        ];
    }
}