<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\ComplaintReplyResource\Pages;
use App\Filament\Ourstudent\Resources\ComplaintReplyResource\RelationManagers;
use App\Filament\Ourstudent\Resources\ComplaintResource\RelationManagers\RepliesRelationManager;
use App\Models\ComplaintReply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ComplaintReplyResource extends Resource
{
    protected static ?string $model = ComplaintReply::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subject')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->required()
                ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject'),
                Tables\Columns\BadgeColumn::make('status')
                    ->enum([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                    ])
                    ->colors([
                        'warning' => 'open',
                        'primary' => 'in_progress',
                        'success' => 'resolved',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            RepliesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaintReplies::route('/'),
            'create' => Pages\CreateComplaintReply::route('/create'),
            'edit' => Pages\EditComplaintReply::route('/{record}/edit'),
        ];
    }
}
