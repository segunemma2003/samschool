<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\AssignmentResource\Pages;
use App\Filament\Ourstudent\Resources\AssignmentResource\RelationManagers;
use App\Models\Assignment;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $userId = Auth::id(); // Simplified way to get the authenticated user ID
            $user = User::find($userId);

            if ($user) {
                $student = Student::whereEmail($user->email)->first();

                if ($student && $student->class) {
                    // Filter records where subject's class_id matches the student's class_id
                    $query->whereHas('subject.class', function ($query) use ($student) {
                        $query->where('id', $student->class->id);
                    });
                }
            }
            })
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('class.name'),
                TextColumn::make('section.section'),
                TextColumn::make('deadline'),
                TextColumn::make('created_at')->since()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'view' => Pages\ViewAssignment::route('/{record}'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
