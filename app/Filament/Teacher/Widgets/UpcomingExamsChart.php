<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\ChartWidget;

class UpcomingExamsChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bubble';
    }
}
