<?php

namespace App\Livewire;

use App\Filament\Teacher\Resources\PsychomotorStudentResource\Pages\PsychomotorStudentDetails;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Livewire\Component;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;

class PsychomotorStudent extends Component implements  HasTable, HasForms
{

    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        return $table
            ->query( Student::query()
            ->whereHas('class', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }))
            ->columns([
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('name')->label('Name')->searchable(),
            TextColumn::make('class.name')->label('Class')->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view-student-psych')->label('View Details')
                ->url(fn ($record) => PsychomotorStudentDetails::generateRoute($record->id))
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.psychomotor-student');
    }
}
