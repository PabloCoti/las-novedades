<?php

namespace App\Filament\Resources\SaleResource\Pages;

use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Models\Sale;
use App\Models\Size;
use App\Models\Color;
use App\Models\Store;
use App\Filament\Resources\SaleResource;

use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generate_sale_report')
                ->label('Generar reporte de ventas')
                ->icon('heroicon-o-document-arrow-down')
                ->form([
                    Components\Grid::make(['sm' => 1, 'md' => 2])
                        ->schema([
                            Components\DatePicker::make('start_date')
                                ->label('Fecha de inicio')
                                ->suffixIcon('heroicon-o-calendar')
                                ->closeOnDateSelection()
                                ->format('d/m/Y')
                                ->native(false)
                                ->required(),
                            Components\DatePicker::make('end_date')
                                ->label('Fecha de fin')
                                ->suffixIcon('heroicon-o-calendar')
                                ->closeOnDateSelection()
                                ->format('d/m/Y')
                                ->native(false)
                                ->required(),
                            Components\Select::make('status')
                                ->label('Estado')
                                ->disablePlaceholderSelection()
                                ->default('all')
                                ->native(false)
                                ->options([
                                    'all' => 'Todos',
                                    1 => 'Vigente',
                                    2 => 'Anulada',
                                ])
                                ->required(),
                            Components\Select::make('store_id')
                                ->label('Tienda')
                                ->options(['all' => 'Todas'] + Store::pluck('name', 'id')->toArray())
                                ->disablePlaceholderSelection()
                                ->default('all')
                                ->native(false)
                                ->required(),
                        ])
                ])
                ->action(function ($data)
                {
                    $file_name = str_replace('/', '-', "Ventas desde {$data['start_date']} hasta {$data['end_date']} .xlsx");
                    $tmp_file = tempnam(sys_get_temp_dir(), $file_name);

                    $row_counter = 2;
                    $product_sizes = Size::pluck('name', 'id');
                    $product_colors = Color::pluck('name', 'id');

                    $spreadsheet = new Spreadsheet();
                    $active_worksheet = $spreadsheet->getActiveSheet();

                    $active_worksheet->setCellValue('A1', 'Fecha');
                    $active_worksheet->setCellValue('B1', 'Estado');
                    $active_worksheet->setCellValue('C1', 'Producto');
                    $active_worksheet->setCellValue('D1', 'Talla');
                    $active_worksheet->setCellValue('E1', 'Color');
                    $active_worksheet->setCellValue('F1', 'Cantidad');
                    $active_worksheet->setCellValue('G1', 'Precio');
                    $active_worksheet->setCellValue('H1', 'Total');

                    Sale::whereBetween('date', [
                        Carbon::createFromFormat('d/m/Y', $data['start_date'])->startOfDay(),
                        Carbon::createFromFormat('d/m/Y', $data['end_date'])->endOfDay()
                    ])
                        ->when($data['status'] !== 'all', function ($query) use ($data)
                        {
                            return $query->where('status', $data['status']);
                        })
                        ->when($data['store_id'] !== 'all', function ($query) use ($data)
                        {
                            return $query->where('store_id', $data['store_id']);
                        })
                        ->each(function ($sale) use (&$row_counter, $product_sizes, $product_colors, $active_worksheet)
                        {
                            $sale->product_sales->each(function ($product_sale) use ($product_sizes, $product_colors, $sale, &$row_counter, $active_worksheet)
                            {
                                $active_worksheet->setCellValue("A$row_counter", $sale->date->format('d/m/Y'));
                                $active_worksheet->setCellValue("B$row_counter", $sale->status === 1 ? 'Vigente' : 'Anulada');
                                $active_worksheet->setCellValue("C$row_counter", $product_sale->product->category_name);
                                $active_worksheet->setCellValue("D$row_counter", $product_sizes[$product_sale->size_id]);
                                $active_worksheet->setCellValue("E$row_counter", $product_colors[$product_sale->color_id]);
                                $active_worksheet->setCellValue("F$row_counter", $product_sale->quantity);
                                $active_worksheet->setCellValue("G$row_counter", $product_sale->price);
                                $active_worksheet->setCellValue("H$row_counter", (float)$product_sale->quantity * (float)$product_sale->price);

                                $row_counter++;
                            });
                        });

                    $writer = new Xlsx($spreadsheet);
                    $writer->save($tmp_file);

                    return response()->download($tmp_file, $file_name)->deleteFileAfterSend(true);
                }),
        ];
    }
}
