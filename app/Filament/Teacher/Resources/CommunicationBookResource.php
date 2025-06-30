<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\CommunicationBookResource\Pages;
use App\Filament\Teacher\Resources\CommunicationBookResource\RelationManagers;
use App\Models\CommunicationBook;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CommunicationBookResource extends Resource
{
    protected static ?string $model = CommunicationBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Communication Books';

    protected static ?string $modelLabel = 'Communication Book';

    protected static ?string $pluralModelLabel = 'Communication Books';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 3;

    // Cache teacher data to avoid repeated queries
    protected static function getCachedTeacher(): ?Teacher
    {
        $cacheKey = 'teacher_' . Auth::id();

        return Cache::remember($cacheKey, 300, function () { // 5 minutes cache
            $user = User::find(Auth::id());
            return $user ? Teacher::with(['arm.class'])->where('email', $user->email)->first() : null;
        });
    }

    // Cache students data to avoid repeated queries
    protected static function getCachedStudents(): array
    {
        $teacher = static::getCachedTeacher();

        if (!$teacher || !$teacher->arm) {
            return [];
        }

        $cacheKey = 'students_' . $teacher->id . '_' . $teacher->arm->id;

        return Cache::remember($cacheKey, 600, function () use ($teacher) { // 10 minutes cache
            $arm = $teacher->arm;

            return Student::select(['id', 'name', 'class_id', 'arm_id'])
                ->whereHas('class', function ($query) use ($arm) {
                    $query->where('class_id', $arm->class_id);
                })
                ->whereHas('arm', function ($query) use ($arm) {
                    $query->where('arm_id', $arm->arm_id);
                })
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        });
    }

    public static function form(Form $form): Form
    {
        $students = static::getCachedStudents();

        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('student_id')
                                    ->options($students)
                                    ->label('👨‍🎓 Student Name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select a student...')
                                    ->helperText('Choose the student for this communication'),

                                Forms\Components\DatePicker::make('date')
                                    ->label('📅 Date')
                                    ->default(now())
                                    ->required()
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Date of communication'),
                            ]),

                        Forms\Components\Section::make('📝 Communication Content')
                            ->description('Write your message to the student/parent')
                            ->schema([
                                RichEditor::make('content')
                                    ->label('')
                                    ->required()
                                    ->fileAttachmentsDisk('s3')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                        'link',
                                        'attachFiles',
                                    ])
                                    ->placeholder('Enter your communication message here...')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->persistCollapsed(false),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $teacher = static::getCachedTeacher();

                if ($teacher) {
                    // Optimize query with proper eager loading and indexing
                    $query->select([
                        'communication_books.*'
                    ])
                    ->with([
                        'student:id,name,class_id,arm_id',
                        'student.class:id,name',
                        'student.arm:id,name',
                        'teacher:id,name'
                    ])
                    ->where('teacher_id', $teacher->id)
                    ->latest('date');
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter()
                    ->size('sm'),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('👨‍🎓 Student')
                    ->searchable(['students.name'])
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Student name copied')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('🏫 Class')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('student.arm.name')
                    ->label('📚 Arm')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('date')
                    ->label('📅 Date')
                    ->date('d M, Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('content')
                    ->label('💬 Preview')
                    ->html()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return strip_tags($record->content);
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('👩‍🏫 Teacher')
                    ->searchable(['teachers.name'])
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-academic-cap'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('⏰ Created')
                    ->dateTime('d M, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                DatePicker::make('date_from')
                                    ->label('From Date')
                                    ->placeholder('Select start date'),
                                DatePicker::make('date_to')
                                    ->label('To Date')
                                    ->placeholder('Select end date'),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date) => $query->whereDate('date', '>=', $date)
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date) => $query->whereDate('date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators['date_from'] = 'From: ' . \Carbon\Carbon::parse($data['date_from'])->format('d M, Y');
                        }
                        if ($data['date_to'] ?? null) {
                            $indicators['date_to'] = 'To: ' . \Carbon\Carbon::parse($data['date_to'])->format('d M, Y');
                        }
                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('student_id')
                    ->label('Filter by Student')
                    ->options(function () {
                        return static::getCachedStudents();
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info')
                        ->icon('heroicon-m-eye'),
                    Tables\Actions\EditAction::make()
                        ->color('warning')
                        ->icon('heroicon-m-pencil-square'),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger')
                        ->icon('heroicon-m-trash'),
                ])
                ->label('Actions')
                ->color('gray')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('No communication books yet')
            ->emptyStateDescription('Start by creating your first communication book entry.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Communication Book')
                    ->icon('heroicon-m-plus'),
            ])
            ->striped()
            ->defaultSort('date', 'desc')
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->deferLoading()
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getEloquentQuery(): Builder
    {
        $teacher = static::getCachedTeacher();

        return parent::getEloquentQuery()
            ->when($teacher, function ($query) use ($teacher) {
                return $query->where('teacher_id', $teacher->id);
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('👨‍🎓 Student Information')
                    ->description('Details about the student')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('student.name')
                                    ->label('Student Name')
                                    ->icon('heroicon-m-user')
                                    ->copyable()
                                    ->weight('bold'),
                                TextEntry::make('student.class.name')
                                    ->label('Class')
                                    ->icon('heroicon-m-building-office-2')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('student.arm.name')
                                    ->label('Arm')
                                    ->icon('heroicon-m-book-open')
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),

                Section::make('💬 Communication Details')
                    ->description('The communication content and metadata')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('date')
                                    ->label('Date')
                                    ->date('l, d F Y')
                                    ->icon('heroicon-m-calendar-days')
                                    ->color('primary'),
                                TextEntry::make('teacher.name')
                                    ->label('Teacher')
                                    ->icon('heroicon-m-academic-cap')
                                    ->badge()
                                    ->color('warning'),
                            ]),
                        TextEntry::make('content')
                            ->label('Message Content')
                            ->html()
                            ->prose()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d M, Y H:i:s')
                                    ->icon('heroicon-m-clock')
                                    ->color('gray'),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d M, Y H:i:s')
                                    ->icon('heroicon-m-arrow-path')
                                    ->color('gray'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunicationBooks::route('/'),
            'create' => Pages\CreateCommunicationBook::route('/create'),
            'view' => Pages\ViewCommunicationBook::route('/{record}'),
            'edit' => Pages\EditCommunicationBook::route('/{record}/edit'),
        ];
    }

    // Add navigation badge to show count
    public static function getNavigationBadge(): ?string
    {
        $teacher = static::getCachedTeacher();

        if (!$teacher) {
            return null;
        }

        $count = Cache::remember(
            'communication_books_count_' . $teacher->id,
            300, // 5 minutes
            fn() => static::getModel()::where('teacher_id', $teacher->id)->count()
        );

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
