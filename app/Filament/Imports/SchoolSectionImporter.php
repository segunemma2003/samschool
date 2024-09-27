<?php

namespace App\Filament\Imports;

use App\Models\SchoolSection;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SchoolSectionImporter extends Importer
{
    protected static ?string $model = SchoolSection::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('section')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('category')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('capacity')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('class_id')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('teacher_id')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('note')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
        ];
    }

    public function resolveRecord(): ?SchoolSection
    {
        // return SchoolSection::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new SchoolSection();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your school section import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
