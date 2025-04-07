<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SchoolInformationResource\Pages;
use App\Filament\App\Resources\SchoolInformationResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\SchoolInformation;
use App\Models\Term;
use Coolsam\SignaturePad\Forms\Components\Fields\SignaturePad;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolInformationResource extends Resource
{
    protected static ?string $model = SchoolInformation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('term_id')
                ->options(Term::all()->pluck('name', 'id'))
                ->preload()
                ->searchable()
                ->label("Term")
                ->required(),
                Forms\Components\Select::make('academic_id')
                ->label('Academy Year')
                ->options(AcademicYear::all()->pluck('title', 'id'))
                ->preload()
                ->searchable(),
                Forms\Components\TextInput::make('school_name')
                    ->label('School Name')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                    ->label('School Email')
                    ->email()
                    ->required()
                   ,

                Forms\Components\TextInput::make('school_address')
                    ->label('School Address')
                    ->required(),

                Forms\Components\TextInput::make('school_principal_name')
                    ->label('Principal Name')
                    ->required(),

                SignaturePad::make('principal_sign'),

                FileUpload::make('school_stamp')
                ->disk('s3'),
                Forms\Components\TextInput::make('school_phone')
                    ->label('School Phone')

                    ->required(),
                Select::make('activate_position')
                ->label('Activate Position')
                ->options([
                    "yes"=>"Yes",
                    "no"=>"No"
                ]),

                    FileUpload::make('school_logo')
                    ->disk('s3'),
                Forms\Components\TextInput::make('school_website')
                    ->label('School Website')
                    ->url()
                    ->required(),

                Forms\Components\DatePicker::make('term_begin')
                    ->label('Term Begins')
                    ->required(),
                Forms\Components\DatePicker::make('term_ends')
                    ->label('Term Ends')
                    ->required(),

                Forms\Components\DatePicker::make('next_term_begins')
                    ->label('Next Term Begins')
                    ->nullable(),

                Forms\Components\TextInput::make('mission')
                    ->label('Motto')
                    ->nullable(),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('term.name')
                ->searchable()
                ->sortable(),
                TextColumn::make('academy.title')
                ->searchable()
                ->sortable(),
                TextColumn::make('school_name')
                ->searchable()
                ->sortable(),
                TextColumn::make('school_address')
                ->searchable()
                ->sortable(),
                TextColumn::make('school_principal_name')
                ->searchable()
                ->sortable(),
                ImageColumn::make('principal_sign'),
                ImageColumn::make('school_stamp'),
                TextColumn::make('term_begin')
                ->searchable()
                ->sortable(),
                TextColumn::make('activate_position')
                ->searchable()
                ->sortable(),
                TextColumn::make('term_ends')
                ->searchable()
                ->sortable(),
                TextColumn::make('next_term_begins')
                ->searchable()
                ->sortable(),
                TextColumn::make('mission')
                ->searchable()
                ->sortable(),
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
            'index' => Pages\ListSchoolInformation::route('/'),
            'create' => Pages\CreateSchoolInformation::route('/create'),
            'view' => Pages\ViewSchoolInformation::route('/{record}'),
            'edit' => Pages\EditSchoolInformation::route('/{record}/edit'),
        ];
    }
}
