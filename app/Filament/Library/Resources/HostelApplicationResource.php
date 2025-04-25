<?php

namespace App\Filament\Library\Resources;

use App\Filament\Library\Resources\HostelApplicationResource\Pages;
use App\Filament\Library\Resources\HostelApplicationResource\RelationManagers;
use App\Models\HostelApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HostelApplicationResource extends Resource
{
    protected static ?string $model = HostelApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('term_id')
                    ->relationship('term', 'name')
                    ->required(),
                Forms\Components\Select::make('academic_year_id')
                    ->relationship('academicYear', 'title')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListHostelApplications::route('/'),
            'create' => Pages\CreateHostelApplication::route('/create'),
            'view' => Pages\ViewHostelApplication::route('/{record}'),
            'edit' => Pages\EditHostelApplication::route('/{record}/edit'),
        ];
    }
}
