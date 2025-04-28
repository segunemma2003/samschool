<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\FundraisingProgramResource\Pages;
use App\Filament\Ourparent\Resources\FundraisingProgramResource\RelationManagers;
use App\Models\FundraisingProgram;
use App\Models\Guardians;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ContributionSubmittedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FundraisingProgramResource extends Resource
{
    protected static ?string $model = FundraisingProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        $user = User::whereId(Auth::id())->first();
            $parent = Guardians::whereEmail($user->email)->first();
            $students = Student::where('guardian_id', $parent->id)->pluck('name', 'id');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('target_amount')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('amount_raised')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('progress')
                    ->formatStateUsing(function ($record) {
                        $percentage = ($record->amount_raised / $record->target_amount) * 100;
                        return number_format($percentage, 2) . '%';
                    }),
                Tables\Columns\TextColumn::make('end_date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => FundraisingProgramResource::getUrl('view', ['record' => $record])),
                Tables\Actions\Action::make('contribute')
                    ->label('Contribute')
                    ->icon('heroicon-o-heart')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('₦'),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'online' => 'Online Payment',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\FileUpload::make('proof')
                            ->label('Payment Proof')
                            ->directory('contribution-proofs')
                            ->visibility('private')
                            ->preserveFilenames()
                            ->required()
                            ->visible(fn (Forms\Get $get) => $get('payment_method') === 'bank_transfer'),
                        Forms\Components\Toggle::make('is_anonymous')
                            ->label('Contribute anonymously'),
                        Forms\Components\Textarea::make('message')
                            ->label('Optional Message'),
                    ])
                    ->action(function (\App\Models\FundraisingProgram $record, array $data) use($parent) {
                        $contribution = $record->contributions()->create([
                            'parent_id' => $parent->id,
                            'amount' => $data['amount'],
                            'payment_method' => $data['payment_method'],
                            'status' => $data['payment_method'] === 'online' ? 'pending' : 'pending',
                            'payment_proof_path' => $data['proof'] ?? null,
                            'is_anonymous' => $data['is_anonymous'],
                            'message' => $data['message'],
                        ]);

                        if ($data['payment_method'] === 'online') {
                            // Prepare data for payment API
                            $paymentData = [
                                'school_id' => $record->school_id,
                                'class' => 'N/A', // Not applicable for programs
                                'reference' => 'PROG-' . $contribution->id,
                                'purpose' => 'Program Contribution: ' . $record->title,
                                'data' => [
                                    'program_id' => $record->id,
                                    'program_name' => $record->title,
                                    'parent_id' => $parent->id,
                                    'parent_name' => $parent->name,
                                    'parent_email' => $parent->email,
                                    'is_anonymous' => $data['is_anonymous'],
                                ],
                                'amount' => $data['amount'],
                                'callback_url' => route('payment.callback'),
                            ];

                            // Call payment API
                            return redirect()->away($this->callPaymentGateway($paymentData));
                        }

                        // For bank transfers, just notify admin
                        $record->notify(new ContributionSubmittedNotification($contribution));
                    }),
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
            RelationManagers\ContributionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundraisingPrograms::route('/'),
            'create' => Pages\CreateFundraisingProgram::route('/create'),
            'view' => Pages\ViewFundraisingProgram::route('/{record}'),
            'edit' => Pages\EditFundraisingProgram::route('/{record}/edit'),
        ];
    }
}
