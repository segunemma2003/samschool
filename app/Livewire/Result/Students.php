<?php

namespace App\Livewire\Result;

use App\Filament\Teacher\Resources\ResultResource\Pages\StudentSubjectResult;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Students extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $userId = Auth::id();
        $user = User::whereId($userId)->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        return $table
        ->query(Subject::where('teacher_id', $teacher->id))
            ->columns([
                TextColumn::make('row_number')
                ->label('S/N') // Label for the serial number column
                ->rowIndex(),  // Automatically generates row numbers
            TextColumn::make('code'),
            TextColumn::make('pass_mark'),
            TextColumn::make('final_mark'),
            TextColumn::make('class.name'),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')->label('View Details')
                ->url(fn ($record) => StudentSubjectResult::generateRoute($record->id))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.result.students');
    }
}
