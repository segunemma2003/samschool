<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\StudentFeeResource\Pages;
use App\Filament\Ourparent\Resources\StudentFeeResource\RelationManagers;
use App\Models\Guardians;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class StudentFeeResource extends Resource
{
    protected static ?string $model = StudentFee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $user = User::whereId(Auth::id())->first();
        $parent = Guardians::whereEmail($user->email)->first();
        $students = Student::where('guardian_id', $parent->id)->pluck('name', 'id');
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->options($students)
                    ->required(),
                Forms\Components\Select::make('class_fee_id')
                    ->relationship('classFee', 'id')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₦'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('paid_at'),
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
            'index' => Pages\ListStudentFees::route('/'),
            'create' => Pages\CreateStudentFee::route('/create'),
            'view' => Pages\ViewStudentFee::route('/{record}'),
            'edit' => Pages\EditStudentFee::route('/{record}/edit'),
        ];
    }
}
