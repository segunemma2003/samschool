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
        $cacheKey = "finance_overview_stats";
        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            return [
                'total_paid_today' => \App\Models\SchoolPayment::whereDate('created_at', today())->sum('amount'),
                'pending_approvals' => \App\Models\SchoolPayment::where('status', 'pending')->count(),
                'outstanding_balances' => \App\Models\SchoolInvoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('balance'),
                'fundraising_programs' => \App\Models\FundraisingProgram::count(),
            ];
        });
        return [
            Stat::make('Total Paid Today', '\u20a6' . number_format($stats['total_paid_today'], 2))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Pending Approvals', $stats['pending_approvals'])
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Outstanding Balances', '\u20a6' . number_format($stats['outstanding_balances'], 2))
                ->icon('heroicon-o-exclamation-circle')
                ->color('danger'),
            Stat::make('Fundraising Progress', $stats['fundraising_programs'] . ' programs')
                ->icon('heroicon-o-gift')
                ->color('info'),
        ];
    }
}
