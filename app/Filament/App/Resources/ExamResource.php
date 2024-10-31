<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ExamResource\Pages;
use App\Filament\App\Resources\ExamResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\SchoolSection;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('academic_year_id')
                ->options(AcademicYear::all()->pluck('title', 'id'))
                ->preload()
                ->label("Academic Year")
                ->searchable(),
                Select::make('school_section_id')
                ->options(SchoolSection::all()->pluck('section', 'id'))
                ->preload()
                ->label("School Section")
                ->searchable(),
                Select::make('subject_id')
                ->options(Subject::all()->pluck('name', 'id'))
                ->preload()
                ->label("Subject")
                ->searchable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academic.title'),
                TextColumn::make('section.section'),
                TextColumn::make('subject.name')
            ])
            ->filters([

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
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
