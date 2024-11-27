<?php

namespace App\Filament\App\Resources\StudentResource\Pages;

use App\Exports\StudentExport;
use App\Filament\App\Resources\StudentResource;
use App\Filament\Imports\StudentImporter;
use App\Models\Student;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {

        return [
            Actions\CreateAction::make(),
            Action::make('export')
            ->label('Export')
                ->action(function (array $data) {
                    $query = \App\Models\Student::query();

                    // Apply search filters if any
                    if (!empty($data['search'] ?? null)) {
                        $search = $data['search'];

                        // Filter across multiple fields
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%')
                              ->orWhere('username', 'like', '%' . $search . '%')
                              ->orWhereHas('class', function ($classQuery) use ($search) {
                                  $classQuery->where('name', 'like', '%' . $search . '%');
                              });
                        });
                    }

                    // Download the filtered data directly
                    return Excel::download(new StudentExport($query), 'students.xlsx');
                         })
                        ->icon('heroicon-s-arrow-down-on-square')
                        ->requiresConfirmation()

            // Actions\ImportAction::make()
            //     ->importer(StudentImporter::class),
        ];
    }
}
