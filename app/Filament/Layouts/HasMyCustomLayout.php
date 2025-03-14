<?php
namespace App\Filament\Layouts;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;

trait HasMyCustomLayout{
    use HasCustomLayout;

    protected function getFormPanelPosition(): string
    {
        return 'left'; // Change the panel position
    }
}
