<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Penyewa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')->required(),
                Forms\Components\TextInput::make('kontak')
                    ->label('Kontak')->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')->email(),
                Forms\Components\Textarea::make('alamat')
                    ->label('Alamat'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('kontak')
                    ->label('Kontak'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')->limit(50),
                Tables\Columns\TextColumn::make('leases_count')
                    ->label('Kontrak Aktif')
                    ->counts('leases'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
