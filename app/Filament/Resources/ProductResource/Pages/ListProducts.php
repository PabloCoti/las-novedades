<?php

namespace App\Filament\Resources\ProductResource\Pages;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Models\Size;
use App\Models\Color;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Filament\Resources\ProductResource;
use App\Models\ProductStockStore;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('report')
                ->label('Generar reporte de inventario')
                ->icon('heroicon-o-document-arrow-down')
                ->form([
                    Components\Grid::make(['sm' => 1, 'md' => 2])
                        ->schema([
                            Components\Select::make('store_id')
                                ->label('Tienda')
                                ->options(['all' => 'Todas'] + Store::pluck('name', 'id')->toArray())
                                ->disablePlaceholderSelection()
                                ->default('all')
                                ->native(false)
                                ->searchable()
                                ->required(),
                            Components\Select::make('category_id')
                                ->label('Tipo')
                                ->options(['all' => 'Todas'] + Category::pluck('name', 'id')->toArray())
                                ->disablePlaceholderSelection()
                                ->default('all')
                                ->native(false)
                                ->searchable()
                                ->required(),
                            Components\Select::make('size_id')
                                ->label('Talla')
                                ->options(['all' => 'Todas'] + Size::pluck('name', 'id')->toArray())
                                ->disablePlaceholderSelection()
                                ->default('all')
                                ->native(false)
                                ->searchable()
                                ->required(),
                            Components\Select::make('color_id')
                                ->label('Color')
                                ->options(['all' => 'Todas'] + Color::pluck('name', 'id')->toArray())
                                ->disablePlaceholderSelection()
                                ->default('all')
                                ->native(false)
                                ->searchable()
                                ->required(),
                        ]),
                ])
                ->action(function ($data)
                {
                    $file_name = now() . ' - reporte de productos.xlsx';
                    $tmp_file = tempnam(sys_get_temp_dir(), $file_name);

                    $row_counter = 2;

                    $stores = Store::pluck('name', 'id');
                    $products = Product::get()->pluck('category_description', 'id');
                    $product_sizes = Size::pluck('name', 'id');
                    $product_colors = Color::pluck('name', 'id');

                    $spreadsheet = new Spreadsheet();
                    $active_worksheet = $spreadsheet->getActiveSheet();

                    $active_worksheet->setCellValue('A1', 'Sucursal');
                    $active_worksheet->setCellValue('B1', 'Tipo');
                    $active_worksheet->setCellValue('C1', 'Talla');
                    $active_worksheet->setCellValue('D1', 'Color');
                    $active_worksheet->setCellValue('E1', 'Cantidad');
                    $active_worksheet->setCellValue('F1', 'Precio');
                    $active_worksheet->setCellValue('G1', 'Precio especial');

                    ProductStockStore::when($data['store_id'] !== 'all', function ($query) use ($data)
                    {
                        $query->where('store_id', $data['store_id']);
                    })
                        ->when($data['category_id'] !== 'all', function ($query) use ($data)
                        {
                            $query->whereHas('product', function ($query) use ($data)
                            {
                                $query->where('category_id', $data['category_id']);
                            });
                        })
                        ->when($data['size_id'] !== 'all', function ($query) use ($data)
                        {
                            $query->where('size_id', $data['size_id']);
                        })
                        ->when($data['color_id'] !== 'all', function ($query) use ($data)
                        {
                            $query->where('color_id', $data['color_id']);
                        })
                        ->each(function ($product) use (&$row_counter, $stores, $products, $product_sizes, $product_colors, $active_worksheet)
                        {
                            $active_worksheet->setCellValue("A$row_counter", $stores[$product->store_id]);
                            $active_worksheet->setCellValue("B$row_counter", $products[$product->product_id]);
                            $active_worksheet->setCellValue("C$row_counter", $product_sizes[$product->size_id]);
                            $active_worksheet->setCellValue("D$row_counter", $product_colors[$product->color_id]);
                            $active_worksheet->setCellValue("E$row_counter", $product->stock);
                            $active_worksheet->setCellValue("F$row_counter", $product->product->price);
                            $active_worksheet->setCellValue("G$row_counter", $product->product->special_price);

                            $row_counter++;
                        });

                    $writer = new Xlsx($spreadsheet);
                    $writer->save($tmp_file);

                    return response()->download($tmp_file, $file_name)->deleteFileAfterSend(true);
                }),
        ];
    }
}
