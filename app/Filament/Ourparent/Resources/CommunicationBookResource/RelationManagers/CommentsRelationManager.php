<?php

namespace App\Filament\Ourparent\Resources\CommunicationBookResource\RelationManagers;

use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
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
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),

                Forms\Components\Hidden::make('parent_id')
                    ->default(null),
                RichEditor::make('content')
                ->required()
                ->fileAttachmentsDisk('s3')
                ->columnSpanFull(),

                Forms\Components\Hidden::make('commentable_type')
                    ->default(function () {
                        return $this->getOwnerRecord()::class;
                    }),

                Forms\Components\Hidden::make('commentable_id')
                    ->default(function () {
                        return $this->getOwnerRecord()->id;
                    }),

            ]);
    }



    public function isReadOnly(): bool
    {
        return false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
            //     Tables\Columns\TextColumn::make('content')
            //     ->formatStateUsing(function ($state, $record) {
            //         $depth = $this->getCommentDepth($record);
            //         $indentation = str_repeat('<span class="ml-4"></span>', $depth);
            //         $icon = $depth > 0 ? '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>' : '';
            //         return $indentation . $icon . $state;
            //     })
            //     ->html()
            //     ->wrap(),
            //     Tables\Columns\TextColumn::make('user.name')
            //     ->label('Author'),
            // Tables\Columns\TextColumn::make('created_at')
            //     ->dateTime()
            //     ->sortable(),

            Tables\Columns\ViewColumn::make('thread')
            ->view('filament.tables.columns.comment-thread')
            ])
            ->defaultSort('created_at', 'asc')
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->modifyQueryUsing(fn (Builder $query) => $query
            ->with(['user', 'replies.user'])
            ->withCount('replies')
            ->whereNull('parent_id'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('reply')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->form([
                        RichEditor::make('content')
                            ->required()
                            ->fileAttachmentsDisk('s3')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->replies()->create([
                            'content' => $data['content'],
                            'user_id' => Auth::id(),
                            'parent_id' => $record->id,
                            'commentable_type' => $record->commentable_type,
                            'commentable_id' => $record->commentable_id,
                        ]);
                    }),
                Tables\Actions\EditAction::make()->visible(fn ($record) => $record->user_id == Auth::id()),
                Tables\Actions\DeleteAction::make()->visible(fn ($record) => $record->user_id == Auth::id())
                    ->action(function (array $data, $record): void {
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getCommentDepth(Comment $comment, int $depth = 0): int
    {
        if ($comment->parent_id === null) {
            return $depth;
        }

        return $this->getCommentDepth($comment->parent, $depth + 1);
    }
}
