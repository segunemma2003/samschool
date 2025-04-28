<?php

namespace App\Filament\Ourparent\Resources\FundraisingProgramResource\Pages;

use App\Filament\Ourparent\Resources\FundraisingProgramResource;
use App\Models\Guardians;
use App\Models\User;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewFundraisingProgram extends ViewRecord
{
    protected static string $resource = FundraisingProgramResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Program Information')
                    ->schema([
                        Components\TextEntry::make('title'),
                        Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('target_amount')
                                    ->money('NGN'),
                                Components\TextEntry::make('amount_raised')
                                    ->money('NGN'),
                                Components\TextEntry::make('progress')
                                    ->formatStateUsing(function ($record) {
                                        $percentage = ($record->amount_raised / $record->target_amount) * 100;
                                        return number_format($percentage, 2) . '%';
                                    }),
                            ]),
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('start_date')
                                    ->date(),
                                Components\TextEntry::make('end_date')
                                    ->date(),
                            ]),
                    ]),

                Tabs::make('Contributions')
                    ->tabs([
                        Tabs\Tab::make('My Contributions')
                            ->schema([
                                Components\RepeatableEntry::make('contributions')
                                    ->hiddenLabel()
                                    ->schema([
                                        Components\Grid::make(4)
                                            ->schema([
                                                Components\TextEntry::make('amount')
                                                    ->money('NGN'),
                                                Components\TextEntry::make('payment_method')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'cash' => 'gray',
                                                        'bank_transfer' => 'primary',
                                                        'online' => 'success',
                                                        default => 'gray',
                                                    }),
                                                Components\TextEntry::make('status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'gray',
                                                    }),
                                                Components\TextEntry::make('created_at')
                                                    ->dateTime(),
                                            ]),
                                    ])
                                    ->getStateUsing(function ($record) {

                                            $user = User::where('id', Auth::id())->first();
                                            $parent = Guardians::where('email', $user->email)->first();
                                            return $record->contributions()
                                            ->where('parent_id', $parent->id)
                                            ->get();
                                    }),
                            ]),

                        Tabs\Tab::make('Program Summary')
                            ->schema([
                                Components\Grid::make(2)
                                    ->schema([
                                        Components\TextEntry::make('total_contributions')
                                            ->label('Total Contributions')
                                            ->formatStateUsing(function ($record) {
                                                return $record->contributions()->count();
                                            }),
                                        Components\TextEntry::make('total_amount')
                                            ->label('Total Raised')
                                            ->formatStateUsing(function ($record) {
                                                return '₦' . number_format($record->amount_raised, 2);
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private function callPaymentGateway(array $data)
    {
        // Implement your payment gateway API call here
        $paymentGatewayUrl = 'https://payment-gateway.example.com/initiate';
        return $paymentGatewayUrl . '?' . http_build_query($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('contribute')
                ->label('Make New Contribution')
                ->icon('heroicon-o-plus')
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
                ->action(function (array $data) {
                    $contribution = $this->getRecord()->contributions()->create([
                        'parent_id' => auth()->user()->parent->id,
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
                            'school_id' => $this->getRecord()->school_id,
                            'class' => 'N/A',
                            'reference' => 'PROG-' . $contribution->id,
                            'purpose' => 'Program Contribution: ' . $this->getRecord()->title,
                            'data' => [
                                'program_id' => $this->getRecord()->id,
                                'program_name' => $this->getRecord()->title,
                                'parent_id' => auth()->user()->parent->id,
                                'parent_name' => auth()->user()->name,
                                'parent_email' => auth()->user()->email,
                                'is_anonymous' => $data['is_anonymous'],
                            ],
                            'amount' => $data['amount'],
                            'callback_url' => route('payment.callback'),
                        ];

                        // Call payment API and redirect
                        return redirect()->away($this->callPaymentGateway($paymentData));
                    }

                    // For bank transfers, notify admin
                    $this->getRecord()->notify(new ContributionSubmittedNotification($contribution));

                    $this->refreshFormData([
                        'amount_raised' => $this->getRecord()->amount_raised + $data['amount'],
                    ]);
                }),
            ];
    }
}
