<?php

namespace App\Filament\Ourstudent\Resources\ComplaintResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('message')
                ->required()
                ->fileAttachmentsDisk('s3')
                ->fileAttachmentsDirectory('complaint-replies')
                ->fileAttachmentsVisibility('public'),
                Forms\Components\Toggle::make('is_admin')
                    ->label('Admin Reply')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = User::whereId(Auth::id())->first();
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                // Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\IconColumn::make('is_admin')
                ->label('Type')
                ->icon(fn ($state) => $state ? 'heroicon-o-shield-check' : 'heroicon-o-user')
                ->color(fn ($state) => $state ? 'primary' : 'secondary')
                ->tooltip(fn ($state) => $state ? 'Admin' : 'User'),
                    // Fix here: Adding null-coalescing operator to ensure a boolean value
                    // ->label(fn ($state): string => ($state ?? false) ? 'Admin' : 'User'),
                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->html(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->form([
                    RichEditor::make('message')
                    ->required()
                    ->fileAttachmentsDisk('s3')
                    ->fileAttachmentsDirectory('complaint-replies')
                    ->fileAttachmentsVisibility('public'),
                Forms\Components\Toggle::make('is_admin')
                    ->label('Admin Reply')
                    ->default(true)
                    ->hidden(fn (): bool => !$user->isAdmin()),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();

                    // Ensure is_admin is a boolean value
                    $data['is_admin'] = (bool)($data['is_admin'] ?? false);

                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
