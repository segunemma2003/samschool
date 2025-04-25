<?php

namespace App\Filament\Library\Widgets;

use App\Models\OnlineLibraryMaterial;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecentMaterialsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [

        ];
    }

    protected int|string|array $columnSpan = 'full';

    public function getRecentMaterials()
    {
        return OnlineLibraryMaterial::latest()
            ->limit(5)
            ->get();
    }
}
