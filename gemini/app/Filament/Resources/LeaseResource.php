<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaseResource\Pages;
use App\Filament\Resources\LeaseResource\RelationManagers;
use App\Models\Lease;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaseResource extends Resource
{
    protected static ?string $model = Lease::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Kontrak Sewa';
    protected static ?string $label = 'Kontrak';
    protected static ?string $pluralLabel = 'Kontrak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Penyewa')
                    ->relationship('tenant', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('property_id')
                    ->label('Properti')
                    ->relationship('property', 'kode_lokasi')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live() // agar bisa update status properti nanti (opsional)
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Opsional: otomatis ubah status properti jadi "Disewa"
                        if ($state) {
                            $property = \App\Models\Property::find($state);
                            if ($property && $property->status !== 'Disewa') {
                                $property->update(['status' => 'Disewa']);
                            }
                        }
                    }),

                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->displayFormat('d F Y')
                    ->native(false), // gunakan datepicker Filament

                Forms\Components\DatePicker::make('tanggal_akhir')
                    ->label('Tanggal Akhir')
                    ->required()
                    ->displayFormat('d F Y')
                    ->native(false),

                Forms\Components\TextInput::make('periode')
                    ->label('Periode Sewa')
                    ->placeholder('Contoh: 1 tahun, 6 bulan')
                    ->required(),

                Forms\Components\TextInput::make('harga_sewa')
                    ->label('Harga Sewa per Bulan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('ppn_persen')
                    ->label('PPN (%)')
                    ->numeric()
                    ->suffix('%')
                    ->default(11.00)
                    ->required(),

                Forms\Components\TextInput::make('ppb_persen')
                    ->label('PPB (%)')
                    ->numeric()
                    ->suffix('%')
                    ->default(0.00),

                // Tagihan Lainnya (Custom JSON)
                Forms\Components\Repeater::make('tagihan_lainnya')
                    ->label('Tagihan Lainnya')
                    ->relationship('tagihan_lainnya') // ❌ TIDAK BISA LANGSUNG — JSON!
                    ->schema([
                        Forms\Components\TextInput::make('nama')->required(),
                        Forms\Components\TextInput::make('jumlah')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn() => false), // ⚠️ Kita nonaktifkan dulu karena JSON

                // Ganti dengan Textarea untuk JSON (sederhana)
                Forms\Components\Textarea::make('tagihan_lainnya')
                    ->label('Tagihan Lainnya (JSON)')
                    ->helperText('Contoh: [{"nama":"Listrik","jumlah":150000}]')
                    ->json(), // ✅ Ini penting agar Filament tahu ini JSON

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan Kontrak')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.nama')
                    ->label('Penyewa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('property.kode_lokasi')
                    ->label('Kode Lokasi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode'),

                Tables\Columns\TextColumn::make('harga_sewa')
                    ->label('Harga/Bulan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('ppn_persen')
                    ->label('PPN %')
                    ->suffix('%'),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('tanggal_akhir')
                    ->label('Akhir')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('property')
                    ->relationship('property', 'kode_lokasi'),
                Tables\Filters\SelectFilter::make('tenant')
                    ->relationship('tenant', 'nama'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListLeases::route('/'),
            'create' => Pages\CreateLease::route('/create'),
            'edit' => Pages\EditLease::route('/{record}/edit'),
        ];
    }
}
