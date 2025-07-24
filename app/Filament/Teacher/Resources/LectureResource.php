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
use Filament\Forms\Get;
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

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar'; // More relevant icon for lectures
    protected static ?string $navigationGroup = 'Academic Management';

    public static function form(Form $form): Form
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::where('email', $user->email)->first();

        return $form
            ->schema([
                Select::make('subject_id')
    ->options(function (Get $get) { // Using Get helper
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::where('email', $user->email)->first();
        // dd($teacher->id);
        return $teacher
            ? Subject::whereHas('teacher',function($q) use($teacher){
                $q->where('id', $teacher->id);
            })->pluck('code', 'id')
            : [];
    })
    ->preload()
    ->label('Subject')
    ->searchable()
    ->required()
   ,
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
                TextColumn::make('subject.code')
                ->label('Subject Code')
                ->searchable()
                ->sortable(),
                TextColumn::make('title')
                ->label('Topic')
                ->searchable()
                ->sortable(),
                TextColumn::make('subject.class.name')
                ->label('Class')
                ->searchable()
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
                ->label('Created')
                ->since(),
                Tables\Columns\BadgeColumn::make('meeting_link')
                    ->label('Has Meeting?')
                    ->colors([
                        'success' => fn($state) => !empty($state),
                        'danger' => fn($state) => empty($state),
                    ])
                    ->formatStateUsing(fn($state) => empty($state) ? 'No' : 'Yes'),
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
            ])
            ->striped(); // Zebra striping for readability
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
