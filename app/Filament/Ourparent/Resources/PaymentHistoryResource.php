<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\PaymentHistoryResource\Pages;
use App\Filament\Ourparent\Resources\PaymentHistoryResource\RelationManagers;
use App\Models\Guardians;
use App\Models\PaymentHistory;
use App\Models\SchoolPayment;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryResource extends Resource
{
    protected static ?string $model = SchoolPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Payment History';

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
            $students = Student::where('guardian_id', $parent->id)->pluck('id');

        return $table
        ->modifyQueryUsing(function (Builder $query) use($students, $parent) {

            $query->whereHas('studentFee', function ($q) use ($parent, $students) {
                $q->whereIn('student_id', $students);
            })
            ->orWhereHas('contribution', function ($q) use ($parent) {
                $q->where('parent_id', $parent->id);
            });
        })
        ->columns([
        Tables\Columns\TextColumn::make('payable_type')
            ->formatStateUsing(fn ($state) => match ($state) {
                'student_fee' => 'School Fee',
                'program_contribution' => 'Program Contribution',
                default => $state,
            }),
        Tables\Columns\TextColumn::make('amount')
            ->money('NGN'),
        Tables\Columns\TextColumn::make('payment_method')
            ->badge()
            ->color(fn (string $state): string => match ($state) {
                'cash' => 'gray',
                'bank_transfer' => 'primary',
                'online' => 'success',
                default => 'gray',
            }),
        Tables\Columns\TextColumn::make('status')
            ->badge()
            ->color(fn (string $state): string => match ($state) {
                'pending' => 'warning',
                'approved' => 'success',
                'rejected' => 'danger',
                default => 'gray',
            }),
        Tables\Columns\TextColumn::make('created_at')
            ->dateTime(),
        Tables\Columns\TextColumn::make('approved_at')
            ->dateTime()

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentHistories::route('/'),
            'create' => Pages\CreatePaymentHistory::route('/create'),
            'edit' => Pages\EditPaymentHistory::route('/{record}/edit'),
        ];
    }
}
