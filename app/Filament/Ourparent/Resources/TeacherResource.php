<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\TeacherResource\Pages;
use App\Filament\Ourparent\Resources\TeacherResource\RelationManagers;
use App\Models\Guardians;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
        ->modifyQueryUsing(function (Builder $query) {
            $userEmail = Auth::user()->email;

            // Retrieve the authenticated guardian
            $guardian = Guardians::where('email', $userEmail)->first();

            if ($guardian) {
                // Get the IDs of the classes of the guardian's children
                $classIds = Student::where('guardian_id', $guardian->id)
                    ->pluck('class_id')
                    ->toArray(); // Convert to array for compatibility

                // Filter the teachers who are class teachers for these classes
                $query->whereHas('classes', function ($query) use ($classIds) {
                    $query->whereIn('id', $classIds);
                });
            } else {
                // If no guardian is found, return an empty result
                $query->whereRaw('0 = 1');
            }
        })
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('classes.name'),
                TextColumn::make('name'),
                TextColumn::make('phone')

            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'view' => Pages\ViewTeacher::route('/{record}'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
