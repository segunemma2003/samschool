<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\LectureResource\Pages;
use App\Filament\Teacher\Resources\LectureResource\Pages\ViewLectures;
use App\Filament\Teacher\Resources\LectureResource\RelationManagers;
use App\Models\Lecture;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class LectureResource extends Resource
{
    protected static ?string $model = Lecture::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::where('email', $user->email)->first();

        return $form
            ->schema([
                Select::make('subject_id')
                ->options(Subject::where('teacher_id', $teacher->id)->pluck('name', 'id'))
                ->preload()
                ->label('Subject')
                ->searchable()
                ->required(),

                Select::make('subject_id')
                ->options(Subject::where('teacher_id', $teacher->id)->pluck('name', 'id'))
                ->preload()
                ->label('Subject')
                ->searchable()
                ->required(),
                TextInput::make('title')
                ->label('Topic')
                ->required(),
                Textarea::make('description')
                ->label('Description')
                ->rows(5)
                ->cols(7)
                ->required(),
                DatePicker::make('date_of_meeting')
                ->required(),
                TimePicker::make('time_of_meeting'),
                TextInput::make('meeting_link')
            ,
                RichEditor::make('note')
                ->columnSpanFull(),
                TextInput::make('other_materials_links')
                ->label("Links to other Materials"),
                FileUpload::make('other_materials')
                ->label("Other materials")
                ->disk('s3')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
                ->modifyQueryUsing(function (Builder $query) {
                    $userId = Auth::id();
                    $user= User::whereId($userId)->first();
                    $teacher = Teacher::where('email', $user->email)->first();
                    $query->where('teacher_id', $teacher->id);
                })
            ->columns([
                TextColumn::make('subject.name')
                ->searchable()
                ->sortable(),
                TextColumn::make('title')
                ->searchable()
                ->sortable(),
                TextColumn::make('subject.class.name')
                ->searchable()
                ->label('Class')
                ->sortable(),
                TextColumn::make('meeting_link')
                ->label('Meeting Link')
                ->copyable()
                ->copyMessage('Meeting link copied'),
                TextColumn::make('date_of_meeting')
                ->label('Date of Meeting')
                ->date(),
                TextColumn::make('time_of_meeting')
                ->label('Time of Meeting')
                ->time(),
                TextColumn::make('created_at')
                ->since()

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListLectures::route('/'),
            'create' => Pages\CreateLecture::route('/create'),
            'edit' => Pages\EditLecture::route('/{record}/edit'),
            'view' => ViewLectures::route('/{record}')
        ];
    }
}
