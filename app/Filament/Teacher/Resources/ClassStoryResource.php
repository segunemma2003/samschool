<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\ClassStoryResource\Pages;
use App\Filament\Teacher\Resources\ClassStoryResource\RelationManagers;
use App\Models\ClassStory;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ClassStoryResource extends Resource
{
    protected static ?string $model = ClassStory::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Stories';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('description')
                ->required()
                ->maxLength(255),

            RichEditor::make('content')
                ->required()
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('published_at')
                ->default(now())
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('class.name')
                    ->label('Class')
                    ->sortable(),
                Tables\Columns\TextColumn::make('arm.name')
                    ->label('Arm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('published_at')
                ->form([DatePicker::make('published_at')])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['published_at'],
                            fn (Builder $query, $date) => $query->whereDate('published_at', $date)
                        );
                }),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Class Story')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title')
                            ,
                            TextEntry::make('description')
                            ->label('Description')
                            ,
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('class.name')
                                    ->label('Class'),
                                TextEntry::make('arm.name')
                                    ->label('Arm'),
                                TextEntry::make('teacher.name')
                                    ->label('Posted By'),
                            ]),
                        TextEntry::make('published_at')
                            ->dateTime(),
                        TextEntry::make('content')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::with('arm')->whereEmail($user->email)->first();
        // Teachers see only their students' communication books


            return $query->where('teacher_id',$teacher->id);

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassStories::route('/'),
            'create' => Pages\CreateClassStory::route('/create'),
            'view' => Pages\ViewClassStory::route('/{record}'),
            'edit' => Pages\EditClassStory::route('/{record}/edit'),
        ];
    }
}
