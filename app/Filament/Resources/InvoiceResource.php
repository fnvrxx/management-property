<?php

namespace App\Filament\Resources;

use App\Exports\InvoicesSummaryExport;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\PaymentHistoriesRelationManager;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Invoice';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('tenant_nama')
                    ->label('Penyewa')
                    ->content(fn ($record) => $record?->lease?->tenant?->nama ?? '-'),
                Forms\Components\Placeholder::make('property_lokasi')
                    ->label('Lokasi')
                    ->content(fn ($record) => $record?->lease?->property?->kode_lokasi ?? '-'),
                Forms\Components\Placeholder::make('bulan_tahun')
                    ->label('Bulan/Tahun')
                    ->content(fn ($record) => $record?->bulan_tahun ?? '-'),
                Forms\Components\Placeholder::make('jumlah_tagihan')
                    ->label('Jumlah Tagihan')
                    ->content(fn ($record) => $record ? 'Rp ' . number_format($record->jumlah_tagihan, 0, ',', '.') : '-'),
                Forms\Components\Placeholder::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->content(fn ($record) => $record?->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-'),

                Forms\Components\Placeholder::make('total_terbayar')
                    ->label('Total Terbayar (via Riwayat)')
                    ->content(fn ($record) => $record
                        ? 'Rp ' . number_format($record->total_terbayar, 0, ',', '.')
                        : '-'),

                Forms\Components\Placeholder::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->content(fn ($record) => $record
                        ? 'Rp ' . number_format($record->sisa_tagihan, 0, ',', '.')
                        : '-'),

                Forms\Components\Select::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'Belum Bayar' => 'Belum Bayar',
                        'Lunas'       => 'Lunas',
                        'Terlambat'   => 'Terlambat',
                    ])->required()->live(),
                Forms\Components\DatePicker::make('tanggal_bayar')
                    ->label('Tanggal Bayar')
                    ->visible(fn ($get) => $get('status_pembayaran') === 'Lunas'),
                Forms\Components\Textarea::make('catatan_pembayaran')
                    ->label('Catatan Pembayaran'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lease.tenant.nama')->label('Penyewa')->searchable(),
                Tables\Columns\TextColumn::make('lease.property.kode_lokasi')->label('Lokasi'),
                Tables\Columns\TextColumn::make('bulan_tahun')->label('Bulan/Tahun'),
                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')->label('Jatuh Tempo')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('jumlah_tagihan')->label('Tagihan')->money('IDR'),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas'      => 'success',
                        'Belum Bayar'=> 'danger',
                        'Terlambat'  => 'warning',
                    }),
                Tables\Columns\TextColumn::make('sisa_hari')
                    ->label('Sisa Hari')
                    ->formatStateUsing(fn ($record) => $record->sisa_hari === null ? '-' : $record->sisa_hari . ' hari')
                    ->color(fn ($record) => match (true) {
                        $record->sisa_hari === null => 'gray',
                        $record->sisa_hari <= 0    => 'danger',
                        $record->sisa_hari <= 3    => 'warning',
                        default                    => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Status')
                    ->options([
                        'Belum Bayar' => 'Belum Bayar',
                        'Lunas'       => 'Lunas',
                        'Terlambat'   => 'Terlambat',
                    ]),
                Tables\Filters\Filter::make('due_soon')
                    ->label('Jatuh Tempo 1 Bulan')
                    ->query(
                        fn (Builder $query) => $query
                            ->where('tanggal_jatuh_tempo', '>=', now())
                            ->where('tanggal_jatuh_tempo', '<=', now()->addMonth())
                            ->where('status_pembayaran', 'Belum Bayar')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Terpilih (.xlsx)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id')->toArray();
                            return Excel::download(
                                new InvoicesSummaryExport($ids),
                                'invoice-terpilih-' . now()->format('Ymd-His') . '.xlsx'
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('tanggal_jatuh_tempo', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            PaymentHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
