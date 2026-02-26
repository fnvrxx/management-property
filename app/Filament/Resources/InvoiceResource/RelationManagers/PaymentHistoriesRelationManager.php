<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentHistories';

    protected static ?string $title = 'Riwayat Pembayaran';

    protected static ?string $label = 'Pembayaran';

    protected static ?string $pluralLabel = 'Riwayat Pembayaran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jenis')
                    ->label('Jenis Pembayaran')
                    ->options([
                        'DP'         => 'DP (Uang Muka)',
                        'Termin'     => 'Termin',
                        'Pelunasan'  => 'Pelunasan',
                    ])
                    ->required()
                    ->default('Termin'),

                Forms\Components\TextInput::make('jumlah_bayar')
                    ->label('Jumlah Dibayar')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->minValue(1),

                Forms\Components\DatePicker::make('tanggal_bayar')
                    ->label('Tanggal Bayar')
                    ->required()
                    ->default(now()),

                Forms\Components\Select::make('metode_bayar')
                    ->label('Metode Pembayaran')
                    ->options([
                        'Transfer Bank' => 'Transfer Bank',
                        'Tunai'         => 'Tunai',
                        'Cek/Giro'      => 'Cek/Giro',
                        'QRIS'          => 'QRIS',
                    ]),

                Forms\Components\TextInput::make('referensi')
                    ->label('No. Referensi / Kode Transaksi')
                    ->placeholder('Contoh: TRF-0226-001'),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('jenis')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_bayar')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DP'        => 'warning',
                        'Termin'    => 'info',
                        'Pelunasan' => 'success',
                    }),

                Tables\Columns\TextColumn::make('jumlah_bayar')
                    ->label('Jumlah Dibayar')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('metode_bayar')
                    ->label('Metode')
                    ->default('-'),

                Tables\Columns\TextColumn::make('referensi')
                    ->label('Referensi')
                    ->default('-'),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(40)
                    ->default('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pembayaran'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('tanggal_bayar', 'desc');
    }
}
