<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Exports\InvoicesSummaryExport;
use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('export_semua')
                ->label('Export Semua (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => Excel::download(
                    new InvoicesSummaryExport(),
                    'invoice-semua-' . now()->format('Ymd-His') . '.xlsx'
                )),

            Actions\Action::make('export_tahun_ini')
                ->label('Export ' . now()->year . ' (.xlsx)')
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->action(fn () => Excel::download(
                    new InvoicesSummaryExport(year: now()->year),
                    'invoice-' . now()->year . '-' . now()->format('Ymd-His') . '.xlsx'
                )),
        ];
    }
}
