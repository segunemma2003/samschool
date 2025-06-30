<?php

namespace App\Filament\Teacher\Resources\CommunicationBookResource\RelationManagers;

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

    protected static ?string $title = '💬 Comments & Responses';

    protected static ?string $modelLabel = 'comment';

    protected static ?string $pluralModelLabel = 'comments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('💬 Comment')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4)
                            ->placeholder('Enter your comment or response...')
                            ->helperText('Maximum 1000 characters allowed')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('visibility')
                                    ->label('👁️ Visibility')
                                    ->options([
                                        'public' => '🌐 Public (Visible to all)',
                                        'private' => '🔒 Private (Teachers only)',
                                        'parent' => '👪 Parent/Guardian only',
                                    ])
                                    ->default('public')
                                    ->required()
                                    ->helperText('Choose who can see this comment'),

                                Forms\Components\Toggle::make('is_important')
                                    ->label('⭐ Mark as Important')
                                    ->helperText('Important comments will be highlighted')
                                    ->default(false),
                            ]),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter()
                    ->size('sm'),

                Tables\Columns\TextColumn::make('content')
                    ->label('💬 Comment')
                    ->limit(100)
                    ->wrap()
                    ->searchable()
                    ->tooltip(function ($record) {
                        return $record->content;
                    }),

                Tables\Columns\BadgeColumn::make('visibility')
                    ->label('👁️ Visibility')
                    ->colors([
                        'success' => 'public',
                        'warning' => 'private',
                        'primary' => 'parent',
                    ])
                    ->icons([
                        'public' => 'heroicon-o-globe-alt',
                        'private' => 'heroicon-o-lock-closed',
                        'parent' => 'heroicon-o-users',
                    ]),

                Tables\Columns\IconColumn::make('is_important')
                    ->label('⭐')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(function ($record) {
                        return $record->is_important ? 'Important comment' : 'Regular comment';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('⏰ Posted')
                    ->dateTime('d M, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('👤 Author')
                    ->default('System')
                    ->icon('heroicon-m-user')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Filter by Visibility')
                    ->options([
                        'public' => '🌐 Public',
                        'private' => '🔒 Private',
                        'parent' => '👪 Parent/Guardian',
                    ]),

                Tables\Filters\TernaryFilter::make('is_important')
                    ->label('Important Comments')
                    ->trueLabel('⭐ Important only')
                    ->falseLabel('📝 Regular only')
                    ->placeholder('🔍 All comments'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Comment')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->modalHeading('💬 Add New Comment')
                    ->modalSubmitActionLabel('Post Comment')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['author_id'] = Auth::id();
                        $data['posted_at'] = now();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info')
                        ->icon('heroicon-m-eye')
                        ->modalHeading('👁️ View Comment'),

                    Tables\Actions\EditAction::make()
                        ->color('warning')
                        ->icon('heroicon-m-pencil-square')
                        ->modalHeading('✏️ Edit Comment'),

                    Tables\Actions\DeleteAction::make()
                        ->color('danger')
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->modalHeading('🗑️ Delete Comment')
                        ->modalDescription('Are you sure you want to delete this comment? This action cannot be undone.'),
                ])
                ->label('Actions')
                ->color('gray')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('🗑️ Delete Selected Comments')
                        ->modalDescription('Are you sure you want to delete the selected comments? This action cannot be undone.'),

                    Tables\Actions\BulkAction::make('mark_important')
                        ->label('⭐ Mark as Important')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_important' => true]);
                            });
                        })
                        ->modalHeading('⭐ Mark Comments as Important')
                        ->modalDescription('Mark the selected comments as important?'),

                    Tables\Actions\BulkAction::make('unmark_important')
                        ->label('Remove Important Mark')
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_important' => false]);
                            });
                        })
                        ->modalHeading('Remove Important Mark')
                        ->modalDescription('Remove the important mark from selected comments?'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('No comments yet')
            ->emptyStateDescription('Be the first to add a comment or response to this communication.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add First Comment')
                    ->icon('heroicon-m-plus'),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->poll('30s') // Auto-refresh every 30 seconds
            ->deferLoading();
    }
}
