<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\RoutineResource\Pages;
use App\Filament\App\Resources\RoutineResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Routine;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Subject;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoutineResource extends Resource
{
    protected static ?string $model = Routine::class;

    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $navigationIcon = 'heroicon-s-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Select::make('academic_id')
                ->label('Academic')
                ->options(AcademicYear::all()->pluck('title', 'id'))
                ->searchable(),
            Forms\Components\Select::make('class_id')
                    ->label('Class Name')
                    ->options(SchoolClass::all()->pluck('name', 'id'))
                    ->searchable(),

            Forms\Components\Select::make('section_id')
                    ->label('Section')
                    ->options(SchoolSection::all()->pluck('section', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->options(Subject::all()->pluck('code', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(Teacher::all()->pluck('name', 'id'))
                    ->searchable(),

                Forms\Components\TextInput::make('room')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('day')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TimePicker::make('start_time')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->required(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('academy.title')
                ->searchable(),
                Tables\Columns\TextColumn::make('room')
                ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                ->searchable(),
                Tables\Columns\TextColumn::make('end_time')
                ->searchable(),
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
            'index' => Pages\ListRoutines::route('/'),
            'create' => Pages\CreateRoutine::route('/create'),
            'view' => Pages\ViewRoutine::route('/{record}'),
            'edit' => Pages\EditRoutine::route('/{record}/edit'),
        ];
    }
}
