<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\LectureResource\Pages;
use App\Filament\Ourstudent\Resources\LectureResource\Pages\ViewLectures;
use App\Filament\Ourstudent\Resources\LectureResource\RelationManagers;
use App\Models\Lecture;
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

class LectureResource extends Resource
{
    protected static ?string $model = Lecture::class;

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
            $userId = Auth::id();
            $user= User::whereId($userId)->first();
            $student = Student::where('email', $user->email)->first();
            if ($student && $student->class) {
                // Filter records where subject's class_id matches the student's class_id
                $query->whereHas('subject.class', function ($query) use ($student) {
                    $query->where('id', $student->class->id);
                });
            }
            // $query->where('id', $student->class->id);
        })
    ->columns([
        TextColumn::make('subject.subjectDepot.name')
        ->searchable()
        ->sortable(),
        TextColumn::make('title')
        ->searchable()
        ->sortable(),
        TextColumn::make('subject.class.name')
        ->searchable()
        ->label('Class')
        ->sortable(),
        TextColumn::make('meeting_link')
        ->label('Meeting Link')
        ->copyable()
        ->copyMessage('Meeting link copied'),
        TextColumn::make('date_of_meeting')
        ->label('Date of Meeting')
        ->date(),
        TextColumn::make('time_of_meeting')
        ->label('Time of Meeting')
        ->time(),
        TextColumn::make('created_at')
        ->since()

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListLectures::route('/'),
            'create' => Pages\CreateLecture::route('/create'),
            'edit' => Pages\EditLecture::route('/{record}/edit'),
            'view' => ViewLectures::route('/{record}'),
        ];
    }
}
