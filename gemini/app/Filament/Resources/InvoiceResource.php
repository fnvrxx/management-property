<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lease.tenant.nama')->label('Penyewa'),
                Tables\Columns\TextColumn::make('lease.property.kode_lokasi')->label('Lokasi'),
                Tables\Columns\TextColumn::make('bulan_tahun'),
                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')->date(),
                Tables\Columns\TextColumn::make('jumlah_tagihan')->money('IDR'),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Bayar' => 'danger',
                        'Terlambat' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('sisa_hari')
                    ->label('Sisa Hari')
                    ->formatStateUsing(fn($record) => $record->sisa_hari . ' hari')
                    ->color(fn($record) => $record->sisa_hari <= 0 ? 'danger' : ($record->sisa_hari <= 3 ? 'warning' : 'gray')),
            ])
            ->filters([
                Tables\Filters\Filter::make('due_soon')
                    ->query(
                        fn(Builder $query) => $query
                            ->where('tanggal_jatuh_tempo', '>=', now())
                            ->where('tanggal_jatuh_tempo', '<=', now()->addDays(7))
                            ->where('status_pembayaran', 'Belum Bayar')
                    ),
            ])
            ->defaultSort('tanggal_jatuh_tempo', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
