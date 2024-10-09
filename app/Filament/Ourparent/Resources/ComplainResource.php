<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\ComplainResource\Pages;
use App\Filament\Ourparent\Resources\ComplainResource\RelationManagers;
use App\Models\Complain;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;

class ComplainResource extends Resource
{
    protected static ?string $model = Complain::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('to_id')
                ->label('Who you Complaining to')
                ->options(User::all()->pluck('name', 'id'))
                ->searchable(),
                Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

                Forms\Components\Textarea::make('description')
                ->required()
                ->maxLength(255),
                // TinyEditor::make('description')
                // // ->fileAttachmentsDisk('cloudinary')
                // // ->fileAttachmentsVisibility('cloudinary')
                // ->required(),

                Forms\Components\FileUpload::make('file')
                ->disk('cloudinary')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            $userId = Auth::user()->id;
            $query->where('user_id', $userId);
        })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->searchable(),
            Tables\Columns\TextColumn::make('complainer.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('user.name')
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
            'index' => Pages\ListComplains::route('/'),
            'create' => Pages\CreateComplain::route('/create'),
            'view' => Pages\ViewComplain::route('/{record}'),
            'edit' => Pages\EditComplain::route('/{record}/edit'),
        ];
    }
}
