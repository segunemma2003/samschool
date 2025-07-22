<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\ChartWidget;

class UpcomingExamsChart extends ChartWidget
{
    protected static ?string $heading = 'Upcoming Exams (Next 30 Days)';

    protected function getData(): array
    {
        // Example data, replace with real query in production
        return [
            'datasets' => [
                [
                    'label' => 'Exams',
                    'data' => [3, 5, 2, 7, 4, 6, 8],
                    'backgroundColor' => 'rgba(37, 99, 235, 0.2)', // blue-600
                    'borderColor' => 'rgba(16, 185, 129, 1)', // green-500
                    'borderWidth' => 2,
                    'tension' => 0.4,
                ],
            ],
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
