<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\GuardianResource\Pages;
use App\Filament\Teacher\Resources\GuardianResource\RelationManagers;
use App\Models\Guardians;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuardianResource extends Resource
{
    protected static ?string $model = Guardians::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label ="Parent";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('father_name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('mother_name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('father_profession')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('mother_profession')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->unique(table: Guardians::class, ignoreRecord: true)
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('phone')
                ->label('Phone number')
                ->tel()
                ->required(),
            Forms\Components\TextInput::make('address')
                ->required()
                ->maxLength(255),
            Forms\Components\FileUpload::make('photo')
                ->disk('s3'),
                    // ->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('username')->unique(table: Guardians::class, ignoreRecord: true)
                ->maxLength(255)
                ->required(),
            Forms\Components\Select::make('user_type')
                ->options([
                    'teacher' => 'teacher',
                    'student' => 'student',
                    'parent' => 'parent',
                    'admin'=>'admin'
                ])->default('parent'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('father_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('mother_name')
                ->sortable(),
            Tables\Columns\TextColumn::make('username')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\ImageColumn::make('photo')->disk('s3')->width(50)->height(50),
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
            'index' => Pages\ListGuardians::route('/'),
            'create' => Pages\CreateGuardian::route('/create'),
            'view' => Pages\ViewGuardian::route('/{record}'),
            'edit' => Pages\EditGuardian::route('/{record}/edit'),
        ];
    }
}
