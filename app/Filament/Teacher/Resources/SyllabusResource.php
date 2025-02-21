<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\SyllabusResource\Pages;
use App\Filament\Teacher\Resources\SyllabusResource\Pages\ViewSyllabus;
use App\Filament\Teacher\Resources\SyllabusResource\RelationManagers;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Syllabus;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SyllabusResource extends Resource
{
    protected static ?string $model = Syllabus::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('class_id')
                    ->label('Class Name')
                    ->options(SchoolClass::all()->pluck('name', 'id'))
                    ->searchable(),

                Forms\Components\Select::make('subject_id')
                    ->label('Subject Name')
                    ->options(Subject::all()->pluck('code', 'id'))
                    ->searchable(),
                Forms\Components\FileUpload::make('file')
                    ->disk('s3')
                        ->required(),
                RichEditor::make('description')
                        ->label('Description')
                        ->columnSpanFull()
                        ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                TextColumn::make('subject.code')->searchable(),
                TextColumn::make('class.name')->searchable()
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
            'index' => Pages\ListSyllabi::route('/'),
            'create' => Pages\CreateSyllabus::route('/create'),
            'edit' => Pages\EditSyllabus::route('/{record}/edit'),
            'view' => ViewSyllabus::route('/{record}')
        ];
    }
}
