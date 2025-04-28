<?php

namespace App\Filament\Finance\Widgets;

use App\Models\FundraisingProgram;
use Filament\Widgets\ChartWidget;

class FundraisingProgress extends ChartWidget
{
    protected static ?string $heading = 'Fundraising Progress';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $programs = FundraisingProgram::get();

        $labels = $programs->pluck('title')->toArray();
        $progress = $programs->map(function ($program) {
            return ($program->amount_raised / $program->target_amount) * 100;
        })->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Progress (%)',
                    'data' => $progress,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
