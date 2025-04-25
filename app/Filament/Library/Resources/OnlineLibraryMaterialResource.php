<?php

namespace App\Filament\Library\Resources;

use App\Filament\Library\Resources\OnlineLibraryMaterialResource\Pages;
use App\Filament\Library\Resources\OnlineLibraryMaterialResource\RelationManagers;
use App\Models\OnlineLibraryMaterial;
use App\Models\OnlineLibraryType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OnlineLibraryMaterialResource extends Resource
{
    protected static ?string $model = OnlineLibraryMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Library';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type_id')
                            ->label('Type')
                            ->relationship('type', 'name')
                            ->required()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\Textarea::make('description'),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('author')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('publisher')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('publication_year')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(now()->year),
                        Forms\Components\TextInput::make('isbn')
                            ->label('ISBN')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('doi')
                            ->label('DOI')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->disk('s3')
                            ->directory('library/covers')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Document File')
                            ->disk('s3')
                            ->required()
                            ->directory('library/documents')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/epub+zip',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                            ])
                            ->afterStateUpdated(function ($state, $set) {
                                $set('file_type', $state->getClientOriginalExtension());
                            })
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('file_type'),
                    ]),

                Forms\Components\Section::make('Categories')
                    ->schema([
                        Forms\Components\Select::make('subjects')
                            ->relationship('subjects', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\Textarea::make('description'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('download_count')
                    ->label('Downloads')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                ->relationship('type', 'name'),
            Tables\Filters\SelectFilter::make('subjects')
                ->relationship('subjects', 'name')
                ->multiple(),
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
            RelationManagers\ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOnlineLibraryMaterials::route('/'),
            'create' => Pages\CreateOnlineLibraryMaterial::route('/create'),
            'view' => Pages\ViewOnlineLibraryMaterial::route('/{record}'),
            'edit' => Pages\EditOnlineLibraryMaterial::route('/{record}/edit'),
        ];
    }
}
