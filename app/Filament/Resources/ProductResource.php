<?php

namespace App\Filament\Resources;

use App\Models\Size;
use App\Models\Color;
use App\Models\Product;
use App\Filament\Resources\ProductResource\Pages;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Resources\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model           = Product::class;
    protected static ?string $navigationIcon  = 'ionicon-shirt-outline';
    protected static ?string $navigationGroup = 'Tienda';

    public static function getNavigationLabel(): string
    {
        return 'Prendas';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Prendas';
    }

    public static function getModelLabel(): string
    {
        return 'prenda';
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
                Columns\TextColumn::make('product_category.name')
                    ->label('Tipo')
                    ->searchable(),
                Columns\TextColumn::make('sizes')
                    ->label('Tallas'),
                Columns\TextColumn::make('colors')
                    ->label('Colores'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('size')
                    ->label('Talla')
                    ->options(Size::all()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data)
                    {
                        if (!$data['value']) return;
                        $query->whereHas('product_sizes', function (Builder $query) use ($data)
                        {
                            $query->where('size_id', $data);
                        });
                    }),
                Tables\Filters\SelectFilter::make('color')
                    ->label('Color')
                    ->options(Color::all()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data)
                    {
                        if (!$data['value']) return;
                        $query->whereHas('product_colors', function (Builder $query) use ($data)
                        {
                            $query->where('color_id', $data);
                        });
                    }),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                ]),
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
