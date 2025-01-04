<?php

namespace App\Filament\App\Resources;

use App\Exports\StudentExport;
use App\Filament\App\Resources\StudentResource\Pages;
use App\Filament\App\Resources\StudentResource\RelationManagers;
use App\Filament\Exports\StudentExporter;
use App\Models\AcademicYear;
use App\Models\Arm;
use App\Models\Guardians;
use App\Models\InvoiceGroup;
use App\Models\InvoiceStudent;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\Term;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('registration_number')
                ->label('Admission Number')
                ->unique(ignoreRecord: true)
                    ->disabled(fn(Student $student) => $student->exists)
                    ->default(fn() => 'STD-' . random_int(100000000, 999999999))
                    ->required()
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                // Forms\Components\TextInput::make('email')
                //     ->email()
                //     ->unique(table: Student::class)
                //     // ->default()
                //     ->required()
                //     ->maxLength(255),
                // Forms\Components\DatePicker::make('date_of_birth')
                //     ->required()
                //    ,
                Forms\Components\Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])->required(),
                Forms\Components\TextInput::make('blood_group')
                    ->maxLength(255),
                Forms\Components\TextInput::make('height')
                ->maxLength(255),
                Forms\Components\TextInput::make('weight')
                ->maxLength(255),
                Forms\Components\Select::make('religion')
                    ->options([
                        'christianity' => 'Christianity',
                        'islam' => 'Islam',
                        'others' => 'Others',
                    ])->required(),
                // Forms\Components\DatePicker::make('joining_date')->required(),
                Forms\Components\DatePicker::make('date_of_birth'),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone number')
                    ->tel()
                    ,
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('state')
                    ->required()
                    ->label('State of Origin')
                    ->maxLength(255),
                // Forms\Components\TextInput::make('country')
                //     ->required()
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('username')->unique(table: Student::class)
                //             ->maxLength(255)->required(),
                // Forms\Components\TextInput::make('optional_subject')
                //             ->required()
                //             ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                // Forms\Components\TextInput::make('roll')
                //     ->required()
                //     ->maxLength(255),
                Forms\Components\Textarea::make('remarks')
                ->label('Medical/Allergies')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('class_id')
                    ->label('Class Name')
                    ->options(SchoolClass::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('guardian_id')
                    ->label('Guardian')
                    ->options(Guardians::all()->pluck('name', 'id'))
                    ->searchable(),
                // Forms\Components\Select::make('section_id')
                //     ->label('Section')
                //     ->options(SchoolSection::all()->pluck('section', 'id'))
                //     ->searchable(),
                Forms\Components\Select::make('arm_id')
                    ->label('Arms')
                    ->options(Arm::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('group_id')
                    ->label('Group')
                    ->options(StudentGroup::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\FileUpload::make('avatar')
                ->label('Passport')
                    ->disk('cloudinary')
                        ->required(),
                Forms\Components\Select::make('user_type')
                        ->options([
                            'teacher' => 'teacher',
                            'student' => 'student',
                            'parent' => 'parent',
                            'admin'=>'admin'
                        ])->default('student'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('username')
                ->searchable(),
                Tables\Columns\TextColumn::make('email')
                ->searchable(),
                Tables\Columns\TextColumn::make('class.name')
                ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('createInvoice')
                ->icon('heroicon-s-credit-card')
                ->label("Pay")
                ->modalHeading("create invoice")
                ->requiresConfirmation()
                ->iconButton()
                ->color('info')
                ->action(function (array $data) {
                    do {
                        $orderCode = 'ORD-' . random_int(100000000, 999999999);
                    } while (InvoiceStudent::where('order_code', $orderCode)->exists());

                    $invoiceStudent = InvoiceStudent::create([
                        'order_code' => $orderCode,
                        'term_id' => $data['term_id'],
                        'academic_id' => $data['academic_id'],
                        'student_id' => $data['student_id'],
                        "amount_owed" =>$data['total_amount'],
                        'total_amount' => $data['total_amount'],
                    ]);

                    foreach ($data['invoice_details'] as $detail) {
                        $invoiceStudent->invoice_details()->create($detail);
                    }
                    Notification::make()
                    ->title('Invoice created Successfully')
                    ->success()
                    ->send();
                })
                ->fillForm(fn($record) => [

                    'student_id' => $record->id,
                ])->form([
                    Fieldset::make('Invoice Parent')
                    ->schema([


                        Select::make('term_id')
                        ->label('Term')
                        ->options(Term::all()->pluck('name', 'id'))
                        ->preload()
                        ->searchable(),

                        Select::make('academic_id')
                        ->label('Academy')
                        ->options(AcademicYear::all()->pluck('title', 'id'))
                        ->preload()
                        ->searchable(),

                        Select::make('student_id')
                        ->label('Student Name')
                        ->options(Student::all()->pluck('name', 'id'))
                        ->preload()
                        ->searchable(),

                        TextInput::make('total_amount')
                        ->numeric()
                        ->live()
                        ->prefix('₦')
                        ->required(),

                    ]),

                    Fieldset::make('Invoice Details')
                    ->schema([
                        Repeater::make('invoice_details')

                    ->schema([
                        Select::make('invoice_group_id')
                        ->label('Name')
                        ->options(InvoiceGroup::all()->pluck('name', 'id'))
                        ->preload()
                        ->searchable(),

                        TextInput::make('amount')
                        ->numeric()
                        ->prefix('₦')
                        ->live('blur')
                        ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.invoice_student_id"/>')))
                        ->required()
        ])->columnSpanFull()
        ->hiddenLabel()
        ->collapsible()
        ->collapsed(fn($record) => $record)
        ->cloneable()
        ->afterStateUpdated(function (callable $get, callable $set) {
            // Calculate the total dynamically
            $details = $get('invoice_details');
            $total = collect($details)->sum('amount'); // Sum the 'amount' field
            $set('total_amount', $total); // Update the total_amount field
        }),
                    ])

                ]),
                Tables\Actions\Action::make('changePassword')
                ->label('Change Password')
                ->action(function (array $data, $record) {
                    // Update the teacher's password
                    $record->update([
                        'password' => Hash::make($data['password']),
                    ]);

                    // Find and update the associated user
                    $user = User::where('email', $record->email)->first();
                    if ($user) {
                        $user->update([
                            'password' => Hash::make($data['password']),
                        ]);

                        Notification::make()
                            ->title('Password changed successfully for  Teacher !')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('User record not found for the associated email!')
                            ->danger()
                            ->send();
                    }
                })
                ->form([
                    Forms\Components\TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->required()
                        ->minLength(8),
                ])
                ->modalHeading('Change Password')
                ->modalSubmitActionLabel('Save')
                ->requiresConfirmation(),


                // ExportAction::make()
                //      ->exporter(StudentExporter::class)
                //      ->modifyQueryUsing(function (Builder $query, array $data) {
                //         if (!empty($data['search'] ?? null)) {
                //             $search = $data['search'];

                //             // Filter across multiple fields
                //             $query->where(function ($q) use ($search) {
                //                 $q->where('name', 'like', '%' . $search . '%')
                //                   ->orWhere('username', 'like', '%' . $search . '%')
                //                   ->orWhereHas('class', function ($classQuery) use ($search) {
                //                       $classQuery->where('name', 'like', '%' . $search . '%');
                //                   });
                //             });
                //         }

                //         return $query;
                //     })
                //      ->fileDisk('cloudinary')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('generateBulkInvoices')
                    ->label('Generate Bulk Invoices')
                    ->requiresConfirmation()
                    ->form([
                        Select::make('term_id')
                            ->label('Term')
                            ->options(Term::all()->pluck('name', 'id'))
                            ->preload()
                            ->searchable()
                            ->required(),

                        Select::make('academic_id')
                            ->label('Academy')
                            ->options(AcademicYear::all()->pluck('title', 'id'))
                            ->preload()
                            ->searchable()
                            ->required(),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->prefix('₦')
                            ->numeric()
                            ->live()
                            ->required(),

                        Repeater::make('invoice_details')
                            ->label('Invoice Details')
                            ->schema([
                                Select::make('invoice_group_id')
                                    ->label('Name')
                                    ->options(InvoiceGroup::all()->pluck('name', 'id'))
                                    ->preload()
                                    ->searchable()
                                    ->required(),

                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->prefix('₦')
                                    ->numeric()
                                    ->live('blur')
                                    ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.invoice_student_id"/>')))
                                    ->required(),
                            ])
                            ->columnSpanFull()
                            ->minItems(1)
                            ->hiddenLabel()
                            ->collapsible()
                            ->collapsed(fn($record) => $record)
                            ->cloneable()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                // Calculate the total dynamically
                                $details = $get('invoice_details');
                                $total = collect($details)->sum('amount'); // Sum the 'amount' field
                                $set('total_amount', $total); // Update the total_amount field
                            })

                            ->required(),
                            ]) ->action(function (array $data,  $records) {
                                foreach ($records as $record) {
                                    // dd($record);
                                    $student = Student::find($record->id);

                                    if (!$student) {
                                        continue;
                                    }

                                    // Generate unique order code
                                    do {
                                        $orderCode = 'ORD-' . random_int(100000000, 999999999);
                                    } while (InvoiceStudent::where('order_code', $orderCode)->exists());

                                    $invoiceStudent = InvoiceStudent::create([
                                        'order_code' => $orderCode,
                                        'term_id' => $data['term_id'],
                                        'academic_id' => $data['academic_id'],
                                        'student_id' => $record->id,
                                        'total_amount' => $data['total_amount'],
                                        'amount_owed' => $data['total_amount'],
                                    ]);

                                    foreach ($data['invoice_details'] as $detail) {
                                        $invoiceStudent->invoice_details()->create($detail);
                                    }
                                }

                                Notification::make()
                                    ->title('Bulk Invoices Created Successfully')
                                    ->success()
                                    ->send();
                            })
                            ->icon('heroicon-s-document')
                            ->color('success'),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
