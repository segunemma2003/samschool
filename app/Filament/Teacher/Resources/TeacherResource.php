<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\TeacherResource\Pages;
use App\Filament\Teacher\Resources\TeacherResource\RelationManagers;
use App\Models\Teacher;
use App\Models\User;
use Coolsam\SignaturePad\Forms\Components\Fields\SignaturePad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->unique(table: Teacher::class, ignoreRecord: true)
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('designation')
                ->required()
                ->maxLength(255),
            Forms\Components\DatePicker::make('date_of_birth'),
            Forms\Components\Select::make('gender')
            ->options([
                'male' => 'Male',
                'female' => 'Female',
            ])->required(),
            Forms\Components\Select::make('religion')
            ->options([
                'christianity' => 'Christianity',
                'islam' => 'Islam',
                'others' => 'Others',
            ])->required(),
            Forms\Components\DatePicker::make('joining_date')->required(),
            Forms\Components\FileUpload::make('avatar')
                ->disk('s3')
                  ,
            Forms\Components\Select::make('user_type')
                  ->options([
                      'teacher' => 'teacher',
                      'student' => 'student',
                      'parent' => 'parent',
                      'admin'=>'admin'
                  ])->default('teacher'),
            Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
            Forms\Components\TextInput::make('username')->unique(table: Teacher::class, ignoreRecord: true)
                    ->maxLength(255)->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('phone')
                ->label('Phone number')
                ->tel()
                ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->searchable(),

            Tables\Columns\TextColumn::make('username')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\ImageColumn::make('avatar')->disk('s3')->width(50)->height(50),
            Tables\Columns\ImageColumn::make('signature')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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


                Tables\Actions\Action::make(' Add signature')
                ->label('Sign')
                ->action(function (array $data, $record) {
                    // Handle the signature action logic
                    // You can save the signature to the database or perform any other action

                    $signatureData = $data['signature']; // This will be the signature image data

                    // Example: Save signature as base64 string in the record
                    $record->update([
                        'signature' => $signatureData, // Ensure your model has a signature column
                    ]);

                    Notification::make()
                        ->title('Signature saved successfully!')
                        ->success()
                        ->send();
                })
                ->form([
                    // Add the Signature Pad field
                    SignaturePad::make('signature')
                    //     ->backgroundColor('white') // Set the background color if necessary
                    //     ->penColor('blue') // Set the pen color
                    //     ->strokeMinDistance(2.0) // Set the minimum stroke distance
                    //     ->strokeMaxWidth(2.5) // Set the max width of the pen stroke
                    //     ->strokeMinWidth(1.0) // Set the minimum width of the pen stroke
                    //     ->strokeDotSize(2.0) // Set the stroke dot size
                    //     ->hideDownloadButtons() // Optionally hide the download buttons

                ])
                ->modalHeading('Add your Signature')
                ->modalSubmitActionLabel('Save Signature')
                ->requiresConfirmation(),
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
