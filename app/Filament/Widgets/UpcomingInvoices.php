<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingInvoices extends BaseWidget
{
    protected static ?string $heading = 'Tagihan Mendekati Jatuh Tempo (1 Bulan ke Depan)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::with(['lease.tenant', 'lease.property'])
                    ->where('status_pembayaran', 'Belum Bayar')
                    ->whereBetween('tanggal_jatuh_tempo', [now(), now()->addMonth()])
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('lease.tenant.nama')
                    ->label('Penyewa')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lease.property.kode_lokasi')
                    ->label('Lokasi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('jumlah_tagihan')
                    ->label('Total Tagihan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('sisa_hari')
                    ->label('Sisa Hari')
                    ->formatStateUsing(fn($record) => $record->sisa_hari === null ? '-' : $record->sisa_hari . ' hari')
                    ->color(fn($record) => match (true) {
                        $record->sisa_hari === null => 'gray',
                        $record->sisa_hari <= 0    => 'danger',
                        $record->sisa_hari <= 3    => 'warning',
                        default                    => 'gray',
                    }),
            ])
            ->paginated([5, 10, 20]);
    }
}