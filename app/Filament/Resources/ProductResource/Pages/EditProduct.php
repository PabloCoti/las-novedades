<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Models\Size;
use App\Models\Color;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductStockStore;
use App\Filament\Resources\ProductResource;

use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Forms\Components;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class EditProduct extends EditRecord
{
    protected static string $view     = 'filament.pages.product.create';
    protected static string $resource = ProductResource::class;

    public $product_id;

    public function mount($record): void
    {
        parent::mount($record);

        $record           = Product::find($record);
        $this->product_id = $record->id;

        $this->form->fill([
            'product_category_id' => $record->product_category->id,
            'price'               => $record->price,
            'special_price'       => $record->special_price,
            'description'         => $record->description,
            'stocks'              => ProductStockStore::where('product_id', $record->id)->get()->groupBy(function ($stock)
            {
                return $stock->stock;
            })->map(function ($grouped_stocks, $stock)
            {
                return [
                    'product_stock_store_id' => $grouped_stocks->pluck('store_id')->toArray(),
                    'product_size_id'        => $grouped_stocks->pluck('size_id')->toArray(),
                    'product_color_id'       => $grouped_stocks->pluck('color_id')->toArray(),
                    'stock'                  => $stock,
                ];
            })->values()->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Detalles de producto')
                        ->schema([
                            Components\Grid::make(['sm' => 1, 'md' => 2])
                                ->schema([
                                    Components\Select::make('product_category_id')
                                        ->label('Categoría')
                                        ->default(fn () => Category::find(1)->id ?? null)
                                        ->options(Category::all()->pluck('name', 'id'))
                                        ->disablePlaceHolderSelection()
                                        ->columnSpan('full')
                                        ->native(false)
                                        ->required(),
                                    Components\TextInput::make('price')
                                        ->label('Precio')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->prefix('Q')
                                        ->required()
                                        ->numeric(),
                                    Components\TextInput::make('special_price')
                                        ->label('Precio especial')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->prefix('Q')
                                        ->numeric(),

                                    Components\Textarea::make('description')
                                        ->label('Description del producto')
                                        ->placeholder('(Opcional) Agrega una descripción del producto')
                                        ->columnSpan('full'),
                                ]),
                        ]),
                    Wizard\Step::make('Existencias de producto')
                        ->schema([
                            Components\Repeater::make('stocks')
                                ->label('Existencias')
                                ->addActionLabel('Agregar existencias')
                                ->minItems(1)
                                ->cloneable()
                                ->schema([
                                    Components\Select::make('product_stock_store_id')
                                        ->label('Tiendas')
                                        ->options(Store::all()->pluck('name', 'id'))
                                        ->native(false)
                                        ->multiple()
                                        ->required()
                                        ->live(),
                                    Components\Select::make('product_size_id')
                                        ->label('Tallas')
                                        ->options(Size::all()->pluck('name', 'id'))
                                        ->native(false)
                                        ->multiple()
                                        ->required(),
                                    Components\Select::make('product_color_id')
                                        ->label('Colores')
                                        ->options(Color::all()->pluck('name', 'id'))
                                        ->native(false)
                                        ->multiple()
                                        ->required(),
                                    Components\TextInput::make('stock')
                                        ->label('Existencias')
                                        ->required()
                                        ->numeric(),
                                ])
                                ->columns(['sm' => 1, 'md' => 2, 'lg' => 4]),
                        ]),
                ])
                    ->columnSpan('full')
                    ->submitAction(new HtmlString(
                        Blade::render(<<<BLADE
                            <x-filament::button wire:click="updateProduct()" size="md">
                                Actualizar
                            </x-filament::button>
                        BLADE)
                    ))
            ]);
    }

    public function updateProduct()
    {
        $this->validate();

        $data = $this->form->getState();

        if (isset($data['stocks']))
        {
            $product = Product::find($this->product_id);
            $product->update([
                'category_id'   => $data['product_category_id'],
                'description'   => $data['description'],
                'price'         => $data['price'],
                'special_price' => $data['special_price'],
            ]);

            $product->product_stock_stores()->delete();

            foreach ($data['stocks'] as $stock)
            {
                foreach ($stock['product_stock_store_id'] as $store)
                {
                    foreach ($stock['product_size_id'] as $size)
                    {
                        if (!$product->product_sizes()->where('size_id', $size)->exists())
                        {
                            $product->product_sizes()->updateOrCreate(
                                ['size_id'  => $size,],
                                ['size_id'  => $size,]
                            );
                        }

                        foreach ($stock['product_color_id'] as $color)
                        {
                            if (!$product->product_colors()->where('color_id', $color)->exists())
                            {
                                $product->product_colors()->updateOrCreate(
                                    ['color_id' => $color,],
                                    ['color_id' => $color,]
                                );
                            }

                            if (!$product->product_stock_stores()->where([
                                'store_id' => $store,
                                'color_id' => $color,
                                'size_id'  => $size,
                            ])->exists())
                            {
                                $product->product_stock_stores()->create([
                                    'store_id' => $store,
                                    'color_id' => $color,
                                    'size_id'  => $size,
                                    'stock'    => $stock['stock'],
                                ]);
                            }
                        }
                    }
                }
            }

            $selected_sizes  = collect($data['stocks'])->pluck('product_size_id')->flatten()->unique();
            $selected_colors = collect($data['stocks'])->pluck('product_color_id')->flatten()->unique();

            $product->product_sizes()->whereNotIn('size_id', $selected_sizes)->delete();
            $product->product_colors()->whereNotIn('color_id', $selected_colors)->delete();

            Notification::make()
                ->title('Producto actualizado')
                ->success()
                ->send();

            return redirect($this->getResource()::getUrl('index'));
        }
        else
        {
            Notification::make()
                ->title('Debes tener al menos una existencia.')
                ->error()
                ->send();
        }
    }
}
