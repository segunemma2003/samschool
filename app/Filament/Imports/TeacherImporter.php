<?php

namespace App\Filament\Imports;

use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('email')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('designation')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('date_of_birth')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('gender')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('religion')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('joining_date')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('address')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('username')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('password')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('phone')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),

        ];
    }

    public function resolveRecord(): ?Teacher
    {

         // Log::info($this->getColumns());
         Log::info($this->data);
         // dd($this->data);
         $user = User::create([
             "name"=> $this->data['name'],
             "email"=> $this->data['email'],
             "password"=>Hash::make($this->data["password"]),
             "username"=>$this->data["username"]
         ]);
        // return Teacher::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Teacher();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your teacher import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
