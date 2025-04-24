<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SchoolStoryResource\Pages;
use App\Filament\App\Resources\SchoolStoryResource\RelationManagers;
use App\Models\SchoolStory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
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

class SchoolStoryResource extends Resource
{
    protected static ?string $model = SchoolStory::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

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
                FileUpload::make('cover_image')
                    ->disk('s3')
                    ->directory('school-story-attachments')
                    ->visibility('public'),
                RichEditor::make('content')
                ->fileAttachmentsDisk('s3')
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
                    ->description('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Posted By')
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
                Section::make('School Story')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title'),
                        TextEntry::make('description'),
                        ImageEntry::make('cover_image')
                            ->disk('s3')
                            ,
                        TextEntry::make('admin.name')
                            ->label('Posted By'),
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


    public static function canCreate(): bool
    {
        $user = User::find(Auth::id());
        return $user->user_type === 'admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolStories::route('/'),
            'create' => Pages\CreateSchoolStory::route('/create'),
            'view' => Pages\ViewSchoolStory::route('/{record}'),
            'edit' => Pages\EditSchoolStory::route('/{record}/edit'),
        ];
    }
}
