<?php

namespace App\Filament\Ourstudent\Resources\BooksResource\Pages;

use App\Filament\Ourstudent\Resources\BooksResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBooks extends CreateRecord
{
    protected static string $resource = BooksResource::class;
}
