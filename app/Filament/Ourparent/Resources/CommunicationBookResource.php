<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\CommunicationBookResource\Pages;
use App\Filament\Ourparent\Resources\CommunicationBookResource\RelationManagers;
use App\Models\CommunicationBook;
use App\Models\Guardians;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CommunicationBookResource extends Resource
{
    protected static ?string $model = CommunicationBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = User::whereId(Auth::id())->first();
        $parent = Guardians::whereEmail($user->email)->first();
        return $table
            ->columns([
                TextColumn::make('No')
                ->rowIndex(),
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('Class')
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.arm.name')
                    ->label('Arm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('student_id')
                ->label('Student Name')
                ->options(Student::where('guardian_id', $parent->id)->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->multiple()
                ->placeholder('Select Student'),

                Filter::make('created_at')
                ->default(now())
                ->form([DatePicker::make('created_at')])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_at'],
                            fn (Builder $query, $date) => $query->whereDate('created_at', $date)
                        );
                }),

            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = User::whereId(Auth::id())->first();
        $parent = Guardians::whereEmail($user->email)->first();
        $classes = Student::whereGuardianId($parent->id)->pluck('id');
        // Parents see only their students' communication books
        return $query->whereIn('student_id',$classes);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Student Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('student.name')
                                    ->label('Student Name'),
                                TextEntry::make('student.class.name')
                                    ->label('Class'),
                                TextEntry::make('student.arm.name')
                                    ->label('Arm'),
                            ]),
                    ]),
                Section::make('Communication')
                    ->schema([
                        TextEntry::make('date')
                            ->date(),
                        TextEntry::make('teacher.name')
                            ->label('Teacher'),
                        TextEntry::make('content')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunicationBooks::route('/'),
            'create' => Pages\CreateCommunicationBook::route('/create'),
            'view' => Pages\ViewCommunicationBook::route('/{record}'),
            'edit' => Pages\EditCommunicationBook::route('/{record}/edit'),
        ];
    }
}
