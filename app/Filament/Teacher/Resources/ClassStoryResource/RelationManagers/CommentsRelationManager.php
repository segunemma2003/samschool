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
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->maxLength(255)->columnSpanFull(),
                    Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),
                    Forms\Components\Hidden::make('commentable_type')
                    ->default(fn() => get_class($this->getOwnerRecord())),

                Forms\Components\Hidden::make('parent_id')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->recordTitleAttribute('content')
        ->columns([
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('')
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('content')
                    ->html()
                    ->formatStateUsing(function ($state, Comment $record) {
                        // Indent based on depth level
                        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $this->getCommentDepth($record));
                        return $indent . $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('')
                    ->dateTime()
                    ->size('sm')
                    ->color('gray'),
            ])->space(1),
        ])
        ->contentGrid([
            'md' => 1,
            'xl' => 1,
        ])
            ->filters([

            ])
            ->headerActions([

                Tables\Actions\CreateAction::make()
                    ->label('Add Comment')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->modalHeading('Add a new comment')
                    ->modalDescription('Enter your comment below.'),
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([


                Tables\Actions\Action::make('reply')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->form([
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->maxLength(1000),
                        Forms\Components\Hidden::make('commentable_type')
                            ->default(fn ($record) => get_class($record->commentable)),
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
                            'commentable_type' => get_class($record->commentable),
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
            ])
            ->defaultSort('created_at', 'asc')
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['replies' => function ($query) {
                    $query->with(['user', 'replies'])->orderBy('created_at', 'asc');
                }])
                ->whereNull('parent_id')
                ->orderBy('created_at', 'asc');
            });
    }


    protected function getCommentDepth(Comment $comment, $depth = 0): int
    {
        if ($comment->parent_id === null) {
            return $depth;
        }

        if ($comment->relationLoaded('parent') && $comment->parent) {
            return $this->getCommentDepth($comment->parent, $depth + 1);
        }

        return $depth;
    }
    public function replyToComment($parentId, $content)
    {
        $parent = Comment::findOrFail($parentId);

        $parent->commentable->comments()->create([
            'content' => $content,
            'user_id' => Auth::id(),
            'parent_id' => $parent->id,
            'commentable_type' => get_class($parent->commentable),
        ]);

        $this->dispatch('refresh'); // refresh the table or modal content
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
