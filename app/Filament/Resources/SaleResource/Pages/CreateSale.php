<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Sale;
use App\Models\Size;
use App\Models\Store;
use App\Models\Color;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductSale;
use App\Models\ProductStockStore;
use App\Filament\Resources\SaleResource;

use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class CreateSale extends CreateRecord
{
    protected static string $view     = 'filament.pages.create';
    protected static string $resource = SaleResource::class;

    public $products       = [];
    public $product_sizes  = [];
    public $product_colors = [];

    public $products_data = null;
    public $customer_data = null;

    public function getProductSizes(): array
    {
        $this->product_sizes = Size::whereHas('product_sizes', function ($query)
        {
            $query->where('product_id', $this->data['product_id']);
        })->pluck('name', 'id')->toArray();

        return $this->product_sizes;
    }

    public function getProductColors(): array
    {
        $this->product_colors = Color::whereHas('product_colors', function ($query)
        {
            $query->where('product_id', $this->data['product_id']);
        })->pluck('name', 'id')->toArray();

        return $this->product_colors;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Detalles de la venta')
                        ->columns(['sm' => 1, 'md' => 2])
                        ->schema([
                            Components\Select::make('customer_id')
                                ->label('Cliente')
                                ->createOptionUsing(fn ($data) => Customer::create($data)->getKey())
                                ->options(Customer::get()->pluck('nit_name', 'id'))
                                ->native(false)
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set)
                                {
                                    $set('customer_name', $state ? Customer::find($state)->name : '');
                                    $this->customer_data = Customer::find($state);
                                })
                                ->createOptionForm([
                                    Components\Grid::make(['sm' => 1, 'md' => 2])
                                        ->schema([
                                            Components\TextInput::make('tributary_number')
                                                ->label('NIT')
                                                ->rules('unique:customers,tributary_number')
                                                ->required()
                                                ->numeric(),
                                            Components\TextInput::make('name')
                                                ->label('Nombre')
                                                ->required(),
                                            Components\TextInput::make('email')
                                                ->label('Correo electrónico')
                                                ->required()
                                                ->email(),
                                            Components\TextInput::make('phone')
                                                ->label('Teléfono')
                                                ->required()
                                                ->tel(),
                                            Components\Toggle::make('special')
                                                ->label('Cliente especial')
                                                ->default(false)
                                                ->inline(false),
                                        ])
                                ]),
                            Components\TextInput::make('customer_name')
                                ->label('Nombre del cliente')
                                ->disabled(fn ($get) => !empty($get('customer_id'))),
                            Components\Select::make('store_id')
                                ->label('Tienda')
                                ->afterStateUpdated(fn (callable $set) => $set('product_id', []))
                                // Might add later, but for now it's better that the user selects a store (TODO)
                                // ->options(fn () => (['all' => 'Todas'] + Store::pluck('name', 'id')->toArray()))
                                ->options(fn () => Store::pluck('name', 'id')->toArray())
                                ->disablePlaceholderSelection()
                                ->native(false)
                                ->required()
                                ->live(),
                            Components\Select::make('product_id')
                                ->label('Producto')
                                ->disabled(fn ($get) => empty($get('store_id')))
                                ->native(false)
                                ->multiple()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state)
                                {
                                    $this->products_data = Product::whereIn('id', $state)->get();
                                })
                                ->options(fn ($get) => Product::when($get('store_id') != 'all', function ($query, $store_id)
                                {
                                    return $query->whereHas('product_stock_stores', function ($query) use ($store_id)
                                    {
                                        $query->where('store_id', $store_id);
                                    });
                                })->get()->pluck('category_description', 'id')->toArray()),
                        ]),
                    Wizard\Step::make('Existencias de producto')
                        ->schema(function (): array
                        {
                            $schema = [];

                            if (!empty($this->data['product_id']))
                            {
                                foreach ($this->data['product_id'] as $product_id)
                                {
                                    $schema[] = Components\Section::make()
                                        ->heading(fn () => Product::find($product_id)->category_description)
                                        ->collapsible()
                                        ->schema([
                                            Components\Repeater::make("$product_id.stocks")
                                                ->label('Existencias')
                                                ->columns(['sm' => 1, 'md' => 2, 'lg' => 3])
                                                ->addActionLabel('Agregar existencias')
                                                ->minItems(1)
                                                ->cloneable()
                                                ->schema([
                                                    Components\Select::make("$product_id.product_size_id")
                                                        ->label('Tallas')
                                                        ->options(fn () => $this->getProductSizes())
                                                        ->native(false)
                                                        ->required()
                                                        ->live(),
                                                    Components\Select::make("$product_id.product_color_id")
                                                        ->label('Colores')
                                                        ->options(fn () => $this->getProductColors())
                                                        ->native(false)
                                                        ->required()
                                                        ->live(),
                                                    Components\TextInput::make("$product_id.stock")
                                                        ->label('Existencias')
                                                        ->minValue(1)
                                                        ->required()
                                                        ->numeric()
                                                        ->maxValue(fn ($get) => ProductStockStore::where('product_id', $product_id)
                                                            ->where('color_id', $get("$product_id.product_color_id"))
                                                            ->where('size_id', $get("$product_id.product_size_id"))
                                                            ->when($this->data['store_id'] != 'all', function ($query)
                                                            {
                                                                $query->where('store_id', $this->data['store_id']);
                                                            })->first()?->stock ?? 99999),
                                                ]),
                                        ]);
                                }
                            }

                            return $schema;
                        }),
                    Wizard\Step::make('Resumen de venta')
                        ->columns(['sm' => 1, 'md' => 2])
                        ->schema(function (): array
                        {
                            $this->products = [];

                            if (!empty($this->data['product_id']))
                            {
                                foreach ($this->data['product_id'] as $product_id)
                                {
                                    if (!empty($this->data[$product_id]['stocks']))
                                    {
                                        $product_stocks = $this->data[$product_id]['stocks'];
                                        foreach ($product_stocks as $stock_details)
                                        {
                                            $product = $stock_details[$product_id];

                                            if (!empty($product['stock']))
                                            {
                                                $price = $this->products_data->firstWhere('id', $product_id)->price;
                                                $stock = (int) $product['stock'];

                                                if (!empty($this->customer_data))
                                                {
                                                    $special_price = $this->products_data->firstWhere('id', $product_id)->special_price;

                                                    if ($this->customer_data->special && !empty($special_price))
                                                    {
                                                        $price = $special_price;
                                                    }
                                                }

                                                $this->products[$product_id] = [
                                                    'name'            => $this->products_data->firstWhere('id', $product_id)->category_name . ', ' . $this->product_sizes[$product['product_size_id']] . ', ' . $this->product_colors[$product['product_color_id']],
                                                    'color_id'        => $product['product_color_id'],
                                                    'product_size_id' => $product['product_size_id'],
                                                    'quantity'        => $stock,
                                                    'price'           => $price,
                                                    'total'           => $stock * $price,
                                                ];
                                            }
                                        }
                                    }
                                }

                                $sale_total = array_sum(array_column($this->products, 'total'));
                                $this->data['total'] = $sale_total;

                                return [
                                    Components\ViewField::make('')
                                        ->view('components.sales.summary')
                                        ->columnSpan('full')
                                        ->viewData([
                                            'products'   => $this->products,
                                            'sale_total' => $sale_total,
                                        ]),
                                ];
                            }

                            return [];
                        }),
                ])
                    ->columnSpan('full')
                    ->submitAction(new HtmlString(
                        Blade::render(<<<BLADE
                            <x-filament::button wire:click="createSale()" size="md">
                                Crear
                            </x-filament::button>
                        BLADE)
                    )),
            ]);
    }

    public function createSale()
    {
        $this->validate();

        if (empty($this->data['customer_id']))
        {
            if (!empty($this->data['customer_name']))
            {
                $customer = Customer::create([
                    'name'             => $this->data['customer_name'],
                    'email'            => '',
                    'phone'            => '',
                    'tributary_number' => 'C/F',
                ]);

                $this->data['customer_id'] = $customer->id;
            }
            else
            {
                $this->data['customer_id'] = 1;
            }
        }

        $sale_id = Sale::create([
            'user_id'     => auth()->id(),
            'store_id'    => $this->data['store_id'] == 'all' ? auth()->user()->store_id : $this->data['store_id'],
            'customer_id' => $this->data['customer_id'],
            'date'        => now(),
            'total'       => $this->data['total'],
        ])->id;

        foreach ($this->products as $product_id => $product)
        {
            ProductSale::create([
                'sale_id'    => $sale_id,
                'product_id' => $product_id,
                'color_id'   => $product['color_id'],
                'size_id'    => $product['product_size_id'],
                'quantity'   => $product['quantity'],
                'price'      => $product['price'],
            ]);

            $product_stock_store = ProductStockStore::where('product_id', $product_id)
                ->where('color_id', $product['color_id'])
                ->where('size_id', $product['product_size_id'])
                ->where('store_id', $this->data['store_id'] == 'all' ? auth()->user()->store_id : $this->data['store_id'])
                ->first();

            if ($product_stock_store)
            {
                $product_stock_store->update([
                    'stock' => max(0, ($product_stock_store->stock ?? 0) - $product['quantity'])
                ]);
            }
            else
            {
                ProductStockStore::create([
                    'product_id' => $product_id,
                    'color_id'   => $product['color_id'],
                    'size_id'    => $product['product_size_id'],
                    'store_id'   => $this->data['store_id'] == 'all' ? auth()->user()->store_id : $this->data['store_id'],
                    'stock'      => 0,
                ]);
            }
        }

        return redirect(SaleResource::getUrl('index'));
    }
}
