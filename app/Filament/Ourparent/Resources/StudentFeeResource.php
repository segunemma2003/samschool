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

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

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
            $students = Student::where('guardian_id', $parent->id)->pluck('id');

        return $table
        ->modifyQueryUsing(function (Builder $query) use($user, $parent, $students) {

            $query->whereIn('student_id', $students);
        })
        ->columns([
            Tables\Columns\TextColumn::make('student.name')
                ->label('Student'),
            Tables\Columns\TextColumn::make('classFee.feeStructure.name')
                ->label('Fee Type'),
            Tables\Columns\TextColumn::make('amount')
                ->money('NGN'),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'partial' => 'info',
                    'paid' => 'success',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('due_date')
                ->date(),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('pay')
                ->label('Pay Now')
                ->icon('heroicon-o-credit-card')
                ->form([
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->default(fn (StudentFee $record) => $record->amount)
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
                        ->directory('payment-proofs')
                        ->visibility('private')
                        ->preserveFilenames()
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('payment_method') === 'bank_transfer'),
                ])
                ->action(function (StudentFee $record, array $data) use($parent) {
                    $payment = $record->payments()->create([
                        'amount' => $data['amount'],
                        'payment_method' => $data['payment_method'],
                        'status' => $data['payment_method'] === 'online' ? 'pending' : 'pending',
                        'payment_proof_path' => $data['proof'] ?? null,
                    ]);

                    if ($data['payment_method'] === 'online') {
                        // Prepare data for payment API
                        $paymentData = [
                            'school_id' => $record->student->school_id,
                            'class' => $record->student->schoolClass->name,
                            'reference' => 'FEE-' . $payment->id,
                            'purpose' => 'School Fees Payment',
                            'data' => [
                                'student_id' => $record->student_id,
                                'student_name' => $record->student->name,
                                'student_email' => $record->student->email,
                                'parent_id' => $parent->id,
                                'parent_name' => $parent->name,
                                'fee_structure_id' => $record->classFee->feeStructure->id,
                                'fee_structure_name' => $record->classFee->feeStructure->name,
                            ],
                            'amount' => $data['amount'],
                            'callback_url' => route('payment.callback'),
                        ];

                        // Call payment API
                        return redirect()->away($this->callPaymentGateway($paymentData));
                    }

                    // For bank transfers, just notify admin
                    // $record->student->notify(new PaymentSubmittedNotification($payment));
                }),
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

    private function callPaymentGateway(array $data)
    {
        // Implement your payment gateway API call here
        // This is a placeholder - replace with your actual payment gateway integration
        $paymentGatewayUrl = 'https://payment-gateway.example.com/initiate';

        // In a real implementation, you might:
        // 1. Generate a hash or signature
        // 2. Send a POST request to the gateway
        // 3. Redirect to the payment page

        return $paymentGatewayUrl . '?' . http_build_query($data);
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
