<?php

namespace App\Filament\Finance\Widgets;

use App\Models\FundraisingProgram;
use App\Models\SchoolInvoice;
use App\Models\SchoolPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Paid Today',
                '₦' . number_format(SchoolPayment::whereDate('created_at', today())->sum('amount'), 2)
            )
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Pending Approvals',
                SchoolPayment::where('status', 'pending')->count()
            )
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Outstanding Balances',
                '₦' . number_format(SchoolInvoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('balance'), 2)
            )
                ->icon('heroicon-o-exclamation-circle')
                ->color('danger'),

            Stat::make('Fundraising Progress',
                FundraisingProgram::count() . ' programs'
            )
                ->icon('heroicon-o-gift')
                ->color('info'),
        ];
    }
}
