<?php

namespace App\Filament\Imports;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SchoolClassImporter extends Importer
{
    protected static ?string $model = SchoolClass::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('class_numeric')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('note')
            ->rules(['required', 'max:255']),
            ImportColumn::make('teacher_id')
            ->rules(['required'])

        ];
    }


    // protected function beforeSave(): void
    // {
    //     $teacher = Teacher::whereEmail($this->data["teacher_id"])->first();
    //     $this->data['teacher_id'] = $teacher->id;
    //     // unset($this->data['teacher_id']);
    //     // Runs before a record is saved to the database.
    // }

    public function resolveRecord(): ?SchoolClass
    {

        // return SchoolClass::create([
        //     "name"=> $this->data["name"],
        //     "class_numeric"=> $this->data["class_numeric"],
        //     "teacher_id"=> $this->data["teacher_id"],
        //     "note"=> $this->data["note"],
        // ]);
        // return SchoolClass::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new SchoolClass();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your school class import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
