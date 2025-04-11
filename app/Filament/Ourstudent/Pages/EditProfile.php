<?php

namespace App\Filament\Ourstudent\Pages;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;

class EditProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.ourstudent.pages.edit-profile';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            TextInput::make('registration_number')
                ->label('Admission Number')
                ->unique(ignoreRecord: true)
                    ->disabled(fn(Student $student) => $student->exists)
                    ->default(fn() => 'STD-' . random_int(100000000, 999999999))
                    ->required()
                    ->columnSpanFull()
                    ->required()
                    ->readOnly(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])->required(),
                TextInput::make('blood_group')
                    ->maxLength(255),
                TextInput::make('height')
                ->maxLength(255),
                TextInput::make('weight')
                ->maxLength(255),
                Select::make('religion')
                    ->options([
                        'christianity' => 'Christianity',
                        'islam' => 'Islam',
                        'others' => 'Others',
                    ])->required(),
                DatePicker::make('date_of_birth'),
                TextInput::make('phone')
                    ->label('Phone number')
                    ->tel()
                    ,
                TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                TextInput::make('state')
                    ->required()
                    ->label('State of Origin')
                    ->maxLength(255),
                Textarea::make('remarks')
                ->label('Medical/Allergies')
                    // ->required()
                    ->maxLength(255),
                FileUpload::make('avatar')
                ->label('Passport')
                    ->disk('s3')
                        ->required(),

            ]);
    }
}
