<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ExamCommentResource\Pages;
use App\Filament\App\Resources\ExamCommentResource\RelationManagers;
use App\Models\ExamComment;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamCommentResource extends Resource
{
    protected static ?string $model = ExamComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Academic';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('exam')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('class_id')
                ->label('Class Name')
                ->options(SchoolClass::all()->pluck('name', 'id'))
                ->searchable(),

            Forms\Components\Select::make('teacher_id')
                ->label('Teacher')
                ->options(Teacher::all()->pluck('name', 'id'))
                ->searchable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam')
                ->searchable(),
                Tables\Columns\TextColumn::make('title')
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
            'index' => Pages\ListExamComments::route('/'),
            'create' => Pages\CreateExamComment::route('/create'),
            'view' => Pages\ViewExamComment::route('/{record}'),
            'edit' => Pages\EditExamComment::route('/{record}/edit'),
        ];
    }
}
