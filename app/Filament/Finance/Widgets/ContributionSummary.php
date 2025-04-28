<?php

namespace App\Filament\Finance\Widgets;

use App\Models\ProgramContribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContributionSummary extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalContributions = ProgramContribution::where('status', 'approved')
            ->count();

        $totalAmount = ProgramContribution::where('status', 'approved')
            ->sum('amount');



        return [
            Stat::make('Total Contributions', $totalContributions)
                ->icon('heroicon-o-gift')
                ->description('Across all programs')
                ->color('success'),

            Stat::make('Total Amount Contributed', '₦' . number_format($totalAmount, 2))
                ->icon('heroicon-o-currency-dollar')
                ->description('Your total donations')
                ->color('primary'),


        ];
    }
}
