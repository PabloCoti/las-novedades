<?php

namespace App\Filament\Resources;

use App\Models\Sale;
use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Filters;
use Filament\Tables\Columns;
use Filament\Tables\Actions;
use Filament\Resources\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model           = Sale::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Tienda';

    public static function getNavigationLabel(): string
    {
        return 'Ventas';
    }

    public static function getPluralModelLabel(): string
    {
        return 'ventas';
    }

    public static function getModelLabel(): string
    {
        return 'venta';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->color(fn ($state) => [
                        1 => 'success',
                        2 => 'danger',
                    ][$state])
                    ->formatStateUsing(fn ($state) => [
                        1 => 'Vigente',
                        2 => 'Anulada',
                    ][$state]),
                Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('date')
                    ->label('Fecha de venta')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('total')
                    ->label('Total')
                    ->searchable()
                    ->sortable(),
            ])->defaultSort('date', 'desc')
            ->filters([
                Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->default(1)
                    ->options([
                        1 => 'Vigente',
                        2 => 'Anulada',
                    ]),
            ])
            ->hiddenFilterIndicators()
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\Action::make('nullify')
                        ->label('Anular')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->color('danger')
                        ->action(function ($record)
                        {
                            $record->product_sales->each(function ($product_sale)
                            {
                                $product_sale->product->product_stock_stores->each(function ($product_stock_store) use ($product_sale)
                                {
                                    $product_stock_store->update(['stock' => $product_stock_store->stock + $product_sale->quantity]);
                                });
                            });

                            $record->update(['status' => 2]);
                        }),
                ])
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
