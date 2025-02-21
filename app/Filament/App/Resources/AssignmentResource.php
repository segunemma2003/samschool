<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AssignmentResource\Pages;
use App\Filament\App\Resources\AssignmentResource\RelationManagers;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('deadline')
                    ->required(),
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
                Forms\Components\FileUpload::make('file')
                    ->disk('s3')
                        ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->searchable(),
                Tables\Columns\TextColumn::make('class.name')
                ->searchable(),

                Tables\Columns\TextColumn::make('section.section')
                ->searchable(),

                Tables\Columns\TextColumn::make('subject.code')
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
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'view' => Pages\ViewAssignment::route('/{record}'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
