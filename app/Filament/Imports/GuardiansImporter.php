<?php

namespace App\Filament\Imports;

use App\Models\Guardians;
use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Filament\Actions\Imports\Jobs\ImportCsv;

class GuardiansImporter extends Importer
{
    protected static ?string $model = Guardians::class;


//     protected function beforeSave(): void
// {
//     Log::info($this->data);
// }

public function getJobRetryUntil(): ?CarbonInterface
{
    return now()->addMinute(1);
}
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('father_name')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('mother_name')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('father_profession')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('mother_profession')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('email')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('phone')
            ->rules(['required'])
            ->requiredMapping(),
            ImportColumn::make('address')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),
            ImportColumn::make('password')
            ->rules(['required'])
            ->requiredMapping(),
            ImportColumn::make('username')
            ->rules(['required', 'max:255'])
            ->requiredMapping(),


        ];
    }

    public function resolveRecord(): ?Guardians
    {
        // Log::info($this->getColumns());
        // Log::info($this->data);
        // dd($this->data);
        $user = User::create([
            "name"=> $this->data['name'],
            "email"=> $this->data['email'],
            "password"=>Hash::make($this->data["password"]),
            "username"=>$this->data["username"]
        ]);
        $guardian = Guardians::create([
            // Update existing records, matching them by `$this->data['column_name']`
            'email' => $this->data['email'],
            'name' => $this->data['name'],
            'father_name' => $this->data['father_name'],
            'mother_name' => $this->data['mother_name'],
            'father_profession'=>$this->data['father_profession'],
            'mother_profession' => $this->data['mother_profession'],
            'phone' => $this->data['phone'],
            'address' => $this->data['address'],
            'username'=> $this->data['username'],
            'password' => Hash::make($this->data['password']),

        ]);

        // $guardian->save();  // Save the record

    return $guardian;

        // return new Guardians();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your guardians import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
