<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Size;
use App\Models\Color;
use App\Filament\Resources\SaleResource;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->columns(['sm' => 1, 'md' => 3])
                    ->schema([
                        Components\TextEntry::make('date')
                            ->label('Fecha de venta')
                            ->date('d/m/Y'),
                        Components\TextEntry::make('customer.tributary_number')
                            ->label('NIT'),
                        Components\TextEntry::make('customer.name')
                            ->label('Cliente'),
                        Components\ViewEntry::make('status')
                            ->view('components.sales.summary')
                            ->viewData($this->getSaleSummary())
                            ->columnSpan('full')
                    ])
            ]);
    }

    public function getSaleSummary(): array
    {
        $sale_summary = [];

        $product_sizes  = Size::pluck('name', 'id');
        $product_colors = Color::pluck('name', 'id');

        $this->record->product_sales->each(function ($product_sale) use (&$sale_summary, $product_sizes, $product_colors)
        {
            $sale_summary['products'][] = [
                'name'  => "{$product_sale->product->category_name}, {$product_sizes[$product_sale->size_id]}, {$product_colors[$product_sale->color_id]}",
                'quantity' => $product_sale->quantity,
                'price'    => $product_sale->price,
                'total'    => (float)$product_sale->quantity * (float)$product_sale->price,
            ];
        });

        $sale_summary['sale_total'] = array_sum(array_column($sale_summary['products'], 'total'));

        return $sale_summary;
    }
}
