<?php

namespace App\Filament\Teacher\Resources\ClassStoryResource\RelationManagers;

use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('content')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),
                Forms\Components\Hidden::make('parent_id')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('From'),
                Tables\Columns\TextColumn::make('content'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('reply')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->form([
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->maxLength(1000),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                        Forms\Components\Hidden::make('parent_id')
                            ->default(fn ($record) => $record->id),
                    ])
                    ->action(function (array $data, Comment $record): void {
                        $record->commentable->comments()->create([
                            'content' => $data['content'],
                            'user_id' => $data['user_id'],
                            'parent_id' => $data['parent_id'],
                        ]);
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Comment $record) => $record->user_id === Auth::id()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Comment $record) => $record->user_id === Auth::id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(function (Builder $query) {
                // Only show top-level comments
                return $query->whereNull('parent_id');
            });
            // ->tree(
            //     reorderAction: false,
            //     collapsible: true,
            // );
    }
}
