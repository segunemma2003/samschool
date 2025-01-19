<?php

namespace App\Filament\Teacher\Resources\StudentResource\Pages;

use App\Exports\StudentExport;
use App\Filament\Teacher\Resources\StudentResource;
use App\Jobs\GenerateBroadSheet;
use App\Models\AcademicYear;
use App\Models\DownloadStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\ExportAction;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('DownloadBroadSheet')
                    ->label('Download BroadSheet')
                    ->icon('heroicon-s-arrow-down-on-square')
                    ->form([
                        Select::make('term_id')
                            ->options(Term::all()->pluck('name', 'id'))
                            ->preload()
                            ->label('Term')
                            ->searchable()
                            ->required(),
                       Select::make('class_id')
                            ->options(SchoolClass::all()->pluck('name', 'id'))
                            ->preload()
                            ->label('Class')
                            ->searchable()
                            ->required(),
                    Select::make('academic_id')
                        ->label('Academy Year')
                        ->options(AcademicYear::all()->pluck('title', 'id'))
                        ->preload()
                        ->searchable(),
                    ]) ->action(function (array $data) {
                        // $selectedRecords = $this->getSelectedRecords();
                        // dd($data);
                        $students = Student::where('class_id', $data['class_id'])->get();
                        $status = DownloadStatus::create([
                            'status'=>'processing',
                            'time'=> time(),
                            'data'=> json_encode($data)
                        ]);
                        GenerateBroadSheet::dispatch($data,$students, $status->id);
                        Notification::make()
                        ->title('Download is processing on the background')
                        ->success()
                        ->send();
                    }),
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
        ];
    }
}
