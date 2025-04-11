<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ArmsTeacherResource\Pages;
use App\Filament\App\Resources\ArmsTeacherResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Arm;
use App\Models\ArmsTeacher;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Term;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArmsTeacherResource extends Resource
{
    protected static ?string $model = ArmsTeacher::class;

    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('class_id')
                ->label('Class Name')
                ->options(SchoolClass::all()->pluck('name', 'id'))
                ->searchable()
                ->preload(),
                Forms\Components\Select::make('arm_id')
                ->label('Arm')
                ->options(Arm::all()->pluck('name', 'id'))
                ->searchable()
                ->preload(),
                Forms\Components\Select::make('teacher_id')
                ->label('Class Teacher Name')
                ->options(Teacher::all()->pluck('name', 'id'))
                ->searchable()
                ->preload(),
                Forms\Components\Select::make('term_id')
                ->label('Term')
                ->options(Term::all()->pluck('name', 'id'))
                ->searchable()
                ->preload(),
                Forms\Components\Select::make('academic_id')
                ->label('Academic Year')
                ->options(AcademicYear::all()->pluck('title', 'id'))
                ->searchable()
                ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('class.name')->label('Class')->searchable()->sortable(),
                TextColumn::make('arm.name')->label('Arm')->searchable()->sortable(),
                TextColumn::make('teacher.name')->label('Class Teacher')->searchable()->sortable(),
                TextColumn::make('term.name')->label('Term')->searchable()->sortable(),
                TextColumn::make('academy.title')->label('Academic Year')->searchable()->sortable()
            ])
            ->filters([
               SelectFilter::make('class_id')
                ->label('Class Name')
                ->options(SchoolClass::all()->pluck('name', 'id'))
                ->searchable()
                ->preload(),
                SelectFilter::make('arm_id')
                ->label('Arm')
                ->options(Arm::all()->pluck('name', 'id'))
                ->searchable()
                ->preload(),

                SelectFilter::make('term_id')
                ->label('Term')
                ->options(Term::all()->pluck('name', 'id'))
                ->searchable()
                ->preload(),
                SelectFilter::make('academic_id')
                ->label('Academic Year')
                ->options(AcademicYear::all()->pluck('title', 'id'))
                ->searchable()
                ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListArmsTeachers::route('/'),
            'create' => Pages\CreateArmsTeacher::route('/create'),
            'view' => Pages\ViewArmsTeacher::route('/{record}'),
            'edit' => Pages\EditArmsTeacher::route('/{record}/edit'),
        ];
    }
}
