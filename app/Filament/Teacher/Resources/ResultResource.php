<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\ResultResource\Pages;
use App\Filament\Teacher\Resources\ResultResource\RelationManagers;
use App\Models\Result;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap'; // More relevant icon for results/marks

    protected static ?string $label = "mark";

    protected static ?string $navigationGroup ="Academic Management";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mark_obtained')
                    ->label('Mark Obtained')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state)
                    ->color(fn($state) => $state >= 70 ? 'success' : ($state < 40 ? 'danger' : 'warning')),
                Tables\Columns\TextColumn::make('mark_obtainable')
                    ->label('Mark Obtainable')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('grade')
                    ->label('Grade')
                    ->colors([
                        'success' => fn($state) => $state === 'A',
                        'warning' => fn($state) => $state === 'B' || $state === 'C',
                        'danger' => fn($state) => in_array($state, ['D', 'F']),
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->wrap()
                    ->tooltip(fn($state) => strlen($state) > 20 ? $state : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('grade')
                    ->label('Grade')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                        'F' => 'F',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped(); // Zebra striping for readability
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
            'index' => Pages\ListResults::route('/'),
            'create' => Pages\CreateResult::route('/create'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
            'view-student-results' => Pages\StudentSubjectResult::route('/{record}/students')
        ];
    }
}
