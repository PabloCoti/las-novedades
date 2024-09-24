<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Models\Size;
use App\Models\Color;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Filament\Resources\ProductResource;

use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Forms\Components;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class CreateProduct extends CreateRecord
{
    protected static string $view     = 'filament.pages.create';
    protected static string $resource = ProductResource::class;

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
                            <x-filament::button wire:click="createProduct()" size="md">
                                Crear
                            </x-filament::button>
                        BLADE)
                    ))
            ]);
    }

    public function createProduct()
    {
        $this->validate();

        $data = $this->form->getState();

        if (isset($data['stocks']))
        {
            $product = Product::create([
                'category_id'   => $data['product_category_id'],
                'description'   => $data['description'],
                'price'         => $data['price'],
                'special_price' => $data['special_price'],
            ]);

            foreach ($data['stocks'] as $stock)
            {
                foreach ($stock['product_stock_store_id'] as $store)
                {
                    foreach ($stock['product_size_id'] as $size)
                    {
                        if (!$product->product_sizes()->where('size_id', $size)->exists())
                        {
                            $product->product_sizes()->create([
                                'size_id'  => $size,
                            ]);
                        }

                        foreach ($stock['product_color_id'] as $color)
                        {
                            if (!$product->product_colors()->where('color_id', $color)->exists())
                            {
                                $product->product_colors()->create([
                                    'color_id' => $color,
                                ]);
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

            Notification::make()
                ->title('Producto creado')
                ->success()
                ->send();

            return redirect($this->getResource()::getUrl('index'));
        }
        else
        {
            Notification::make()
                ->title('Debes insertar al menos una existencia')
                ->danger()
                ->send();
        }
    }
}
