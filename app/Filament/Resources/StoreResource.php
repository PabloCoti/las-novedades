<?php

namespace App\Filament\Resources;

use App\Models\Store;
use App\Filament\Resources\StoreResource\Pages;
use App\Models\ProductStockStore;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Forms\Components;
use Filament\Resources\Resource;

class StoreResource extends Resource
{
    protected static ?string $model           = Store::class;
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Configuración';


    public static function getNavigationLabel(): string
    {
        return 'Sucursales';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Sucursales';
    }

    public static function getModelLabel(): string
    {
        return 'sucursal';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Components\TextInput::make('phone')
                    ->label('Número de teléfono')
                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    ->required(),
                Components\TextInput::make('address')
                    ->label('Dirección')
                    ->columnSpan('full')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('phone')
                    ->label('Número de teléfono')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                    Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->hidden(fn ($record) => ProductStockStore::where('store_id', $record->id)->count() != 0),
                ])
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
        ];
    }
}
