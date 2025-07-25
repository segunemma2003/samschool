<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\EbookResource\Pages;
use App\Filament\Teacher\Resources\EbookResource\RelationManagers;
use App\Models\Ebooks;
use App\Models\Subject;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class EbookResource extends Resource
{
    protected static ?string $model = Ebooks::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Library & Digital Resources';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('subject_id')
                ->options(Subject::all()->pluck('subjectDepot.name', 'id'))->preload()
                ->label('Subject')
                ->searchable(),
                TextInput::make('title')
                ->required(),
                Forms\Components\Textarea::make('description')
                ->required(),
                TextInput::make('link')
                ->required()
                ,
                Forms\Components\FileUpload::make('file')
                ->disk('s3')
                ->visibility('public') // if nee->visibility('public') // if needed
->directory('ebooks')
->directory('ebooks')
                ->openable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
    ->rowIndex(),
                TextColumn::make('title')
                ->description('description')->limit(50),
                TextColumn::make('link')->copyable()
                ->copyableState(fn (string $state): string => "URL: {$state}"),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('preview')
                ->label('Preview File')
                ->icon('heroicon-o-eye')
                ->modalHeading('File Preview')
                ->modalContent(function ($record) {
                    $url = Storage::disk('s3')->temporaryUrl($record->file, now()->addMinutes(10));
                    $ext = pathinfo($record->file, PATHINFO_EXTENSION);
                    // dd($url);
                    return view('components.ebook-preview-modal', [
                        'url' => $url,
                        'ext' => $ext,
                    ]);
                }),

            Action::make('download')
                ->label('Download File')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn ($record) => Storage::disk('s3')->temporaryUrl($record->file, now()->addMinutes(10)))
                ->openUrlInNewTab(),
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
            'index' => Pages\ListEbooks::route('/'),
            'create' => Pages\CreateEbook::route('/create'),
            'view' => Pages\ViewEbook::route('/{record}'),
            'edit' => Pages\EditEbook::route('/{record}/edit'),
        ];
    }
}
