<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Nuevos clientes en el último mes', \App\Models\Customer::where('created_at', '>=', now()->subMonth())->count())
                ->description($this->getCustomerGrowthDescription())
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Nuevas ventas en el último mes', \App\Models\Sale::where('created_at', '>=', now()->subMonth())->count())
                ->description($this->getSalesGrowthDescription())
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
        ];
    }

    private function getSalesGrowthDescription(): string
    {
        $currentMonthCount = \App\Models\Sale::where('created_at', '>=', now()->subMonth())->count();
        $previousMonthCount = \App\Models\Sale::whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();

        if ($previousMonthCount == 0)
        {
            return '';
        }

        $growth = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;

        return $growth > 0 ? 'Growth: ' . round($growth, 2) . '%' : 'Decline: ' . round(abs($growth), 2) . '%';
    }

    private function getCustomerGrowthDescription(): string
    {
        $currentMonthCount = \App\Models\Customer::where('created_at', '>=', now()->subMonth())->count();
        $previousMonthCount = \App\Models\Customer::whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();

        if ($previousMonthCount == 0)
        {
            return '';
        }

        $growth = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;

        return $growth > 0 ? 'Growth: ' . round($growth, 2) . '%' : 'Decline: ' . round(abs($growth), 2) . '%';
    }
}
