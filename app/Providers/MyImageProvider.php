<?php

namespace App\Providers;

use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;

class MyImageProvider implements ProvidesImages
{
    protected string $imagePath;

    public function __construct(string $imagePath)
    {
        $this->imagePath = $imagePath;
    }

    public static function make(string $imagePath): static
    {
        return new static(asset($imagePath)); // Pass the asset() image path
    }

    public function getImage(): Image
    {
        return new Image(
            $this->imagePath,
            'Custom background image'
        );
    }
}
