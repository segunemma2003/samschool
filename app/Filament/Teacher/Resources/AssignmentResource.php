<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\AssignmentResource\Pages;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\SubmittedStudents;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\ViewAssignmentSubmission;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Assignments';

    protected static ?string $modelLabel = 'Assignment';

    protected static ?string $pluralModelLabel = 'Assignments';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Academic Management';

    // Cache teacher data for better performance
    protected static ?Teacher $currentTeacher = null;

    protected static function getCurrentTeacher(): ?Teacher
    {
        if (static::$currentTeacher) {
            return static::$currentTeacher;
        }

        $user = Auth::user();
        if (!$user) {
            return null;
        }

        static::$currentTeacher = Cache::remember(
            "teacher_data_{$user->id}",
            600,
            fn() => Teacher::where('email', $user->email)->first()
        );

        return static::$currentTeacher;
    }

    // Helper method to safely get options from a collection
    protected static function getSafeOptions(Collection $collection, string $labelField, string $valueField = 'id'): array
    {
        return $collection
            ->filter(function ($item) use ($labelField) {
                $label = $item->{$labelField};
                return $label !== null && trim($label) !== '';
            })
            ->mapWithKeys(function ($item) use ($labelField, $valueField) {
                $label = trim($item->{$labelField});
                $value = $item->{$valueField};
                return [$value => $label];
            })
            ->toArray();
    }

    public static function form(Form $form): Form
    {
        $teacher = static::getCurrentTeacher();
        $teacherId = $teacher?->id;

        return $form
            ->schema([
                Section::make('Assignment Details')
                    ->description('Create and manage your class assignments')
                    ->icon('heroicon-m-clipboard-document-list')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Assignment Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Math Quiz Chapter 5')
                                    ->columnSpan(2)
                                    ->live(onBlur: true),

                                DateTimePicker::make('deadline')
                                    ->label('Submission Deadline')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('M j, Y g:i A')
                                    ->seconds(false)
                                    ->minDate(now())
                                    ->columnSpan(1),

                                TextInput::make('weight_mark')
                                    ->label('Total Marks')
                                    ->numeric()
                                    ->required()
                                    ->default(100)
                                    ->minValue(1)
                                    ->maxValue(1000)
                                    ->suffix('points')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Class Assignment')
                    ->description('Select which class and subject this assignment is for')
                    ->icon('heroicon-m-academic-cap')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('class_id')
                                    ->label('Class')
                                    ->options(function () use ($teacherId) {
                                        if (!$teacherId) {
                                            return ['0' => 'No classes available'];
                                        }

                                        try {
                                            $classes = Cache::remember(
                                                "teacher_{$teacherId}_classes_safe",
                                                300,
                                                function () use ($teacherId) {
                                                    return SchoolClass::where('teacher_id', $teacherId)
                                                        ->get(['id', 'name']);
                                                }
                                            );

                                            $options = static::getSafeOptions($classes, 'name');

                                            return !empty($options) ? $options : ['0' => 'No classes available'];
                                        } catch (\Exception $e) {
                                            return ['0' => 'Error loading classes'];
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Clear subject when class changes
                                        $set('subject_id', null);
                                    })
                                    ->columnSpan(1)
                                    ->placeholder('Select a class')
                                    ->disableOptionWhen(fn (string $value): bool => $value === '0'),

                                Select::make('subject_id')
                                    ->label('Subject')
                                    ->options(function () use ($teacherId) {
                                        if (!$teacherId) {
                                            return ['0' => 'No subjects available'];
                                        }

                                        try {
                                            $subjects = Cache::remember(
                                                "teacher_{$teacherId}_subjects_safe",
                                                300,
                                                function () use ($teacherId) {
                                                    return Subject::where('teacher_id', $teacherId)
                                                        ->get(['id', 'name', 'code']);
                                                }
                                            );

                                            if ($subjects->isEmpty()) {
                                                return ['0' => 'No subjects available'];
                                            }

                                            $options = [];
                                            foreach ($subjects as $subject) {
                                                $name = trim($subject->name ?? '');
                                                $code = trim($subject->code ?? '');

                                                if (empty($name) && empty($code)) {
                                                    continue; // Skip completely empty subjects
                                                }

                                                $label = $name ?: $code ?: 'Unnamed Subject';
                                                if ($name && $code && $name !== $code) {
                                                    $label = "{$name} ({$code})";
                                                }

                                                $options[$subject->id] = $label;
                                            }

                                            return !empty($options) ? $options : ['0' => 'No valid subjects'];
                                        } catch (\Exception $e) {
                                            return ['0' => 'Error loading subjects'];
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1)
                                    ->placeholder('Select a subject')
                                    ->disableOptionWhen(fn (string $value): bool => $value === '0'),

                                Select::make('term_id')
                                    ->label('Term')
                                    ->options(function () {
                                        try {
                                            $terms = Cache::remember(
                                                'active_terms_safe',
                                                600,
                                                function () {
                                                    return Term::get(['id', 'name']);
                                                }
                                            );

                                            $options = static::getSafeOptions($terms, 'name');

                                            return !empty($options) ? $options : ['0' => 'No terms available'];
                                        } catch (\Exception $e) {
                                            return ['0' => 'Error loading terms'];
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1)
                                    ->placeholder('Select a term')
                                    ->disableOptionWhen(fn (string $value): bool => $value === '0'),

                                Select::make('academic_id')
                                    ->label('Academic Year')
                                    ->options(function () {
                                        try {
                                            $academicYears = Cache::remember(
                                                'academic_years_safe',
                                                600,
                                                function () {
                                                    return AcademicYear::get(['id', 'title']);
                                                }
                                            );

                                            $options = static::getSafeOptions($academicYears, 'title');

                                            return !empty($options) ? $options : ['0' => 'No academic years available'];
                                        } catch (\Exception $e) {
                                            return ['0' => 'Error loading academic years'];
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1)
                                    ->placeholder('Select academic year')
                                    ->disableOptionWhen(fn (string $value): bool => $value === '0'),
                            ]),
                    ]),

                Section::make('Assignment Content')
                    ->description('Provide detailed instructions and attach necessary files')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Assignment Instructions')
                            ->required()
                            ->placeholder('Provide clear instructions for your students...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'heading',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'codeBlock',
                            ])
                            ->columnSpanFull(),

                        FileUpload::make('file')
                            ->label('Assignment Files')
                            ->disk('s3')
                            ->directory('assignments')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/*',
                                'text/plain'
                            ])
                            ->maxSize(10240) // 10MB
                            ->uploadingMessage('Uploading assignment file...')
                            ->helperText('Upload supporting documents, images, or reference materials (Max: 10MB)')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Assignment Settings')
                    ->description('Configure assignment availability and grading')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Assignment Status')
                                    ->options([
                                        'draft' => '📝 Draft',
                                        'available' => '✅ Available to Students',
                                        'closed' => '🔒 Closed for Submissions',
                                    ])
                                    ->default('available')
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('allow_late_submission')
                                    ->label('Late Submissions')
                                    ->options([
                                        '0' => '❌ Not Allowed',
                                        '1' => '⚠️ Allowed with Penalty',
                                        '2' => '✅ Allowed without Penalty',
                                    ])
                                    ->default('0')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                if ($user) {
                    $teacher = Teacher::where('email', $user->email)->first();
                    if ($teacher) {
                        $query->with(['class', 'subject', 'term', 'academy'])
                              ->where('teacher_id', $teacher->id);
                    }
                }
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('title')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->searchable()
                            ->size(TextColumn\TextColumnSize::Large)
                            ->icon('heroicon-m-clipboard-document-list')
                            ->grow(false)
                            ->formatStateUsing(fn (?string $state): string => $state ?: 'Untitled Assignment'),

                        BadgeColumn::make('status')
                            ->colors([
                                'warning' => 'draft',
                                'success' => 'available',
                                'danger' => 'closed',
                                'gray' => fn ($state) => !in_array($state, ['draft', 'available', 'closed']),
                            ])
                            ->icons([
                                'draft' => 'heroicon-m-document',
                                'available' => 'heroicon-m-check-circle',
                                'closed' => 'heroicon-m-lock-closed',
                            ])
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'draft' => 'Draft',
                                'available' => 'Available',
                                'closed' => 'Closed',
                                null => 'Unknown',
                                default => ucfirst($state),
                            }),
                    ]),

                    TextColumn::make('excerpt')
                        ->getStateUsing(function ($record): string {
                            if ($record->description) {
                                return \Str::limit(strip_tags($record->description), 80);
                            }
                            return 'No description provided';
                        })
                        ->color('gray')
                        ->size(TextColumn\TextColumnSize::Small),

                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('class.name')
                            ->label('Class')
                            ->icon('heroicon-m-academic-cap')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->color('gray')
                            ->grow(false)
                            ->formatStateUsing(fn (?string $state): string => $state ?: 'No class assigned'),

                        TextColumn::make('subject_display')
                            ->label('Subject')
                            ->getStateUsing(function ($record): string {
                                if (!$record->subject) {
                                    return 'No subject assigned';
                                }

                                $name = trim($record->subject->name ?? '');
                                $code = trim($record->subject->code ?? '');

                                if ($name && $code && $name !== $code) {
                                    return "{$code} ({$name})";
                                }

                                return $code ?: $name ?: 'Unnamed subject';
                            })
                            ->icon('heroicon-m-book-open')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->color('gray')
                            ->grow(false),

                        TextColumn::make('weight_mark')
                            ->label('Marks')
                            ->formatStateUsing(fn (?int $state): string => $state ? "{$state} pts" : '0 pts')
                            ->icon('heroicon-m-trophy')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->color('gray')
                            ->grow(false),

                        IconColumn::make('file')
                            ->label('Has File')
                            ->boolean()
                            ->trueIcon('heroicon-m-paper-clip')
                            ->falseIcon('')
                            ->trueColor('success')
                            ->size(IconColumn\IconColumnSize::Small)
                            ->grow(false),
                    ]),

                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('total_students_answered')
                            ->label('Submissions')
                            ->icon('heroicon-m-users')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->color('info')
                            ->grow(false)
                            ->formatStateUsing(fn (?int $state): string => (string) ($state ?? 0)),

                        TextColumn::make('deadline')
                            ->label('Due')
                            ->since()
                            ->icon(function ($record) {
                                if (!$record->deadline) return 'heroicon-m-clock';
                                return $record->deadline->isPast() ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-clock';
                            })
                            ->color(function ($record) {
                                if (!$record->deadline) return 'gray';
                                return $record->deadline->isPast() ? 'danger' : 'gray';
                            })
                            ->size(TextColumn\TextColumnSize::Small)
                            ->grow(false)
                            ->formatStateUsing(fn ($state) => $state ? $state->since() : 'No deadline'),

                        TextColumn::make('created_at')
                            ->label('Created')
                            ->since()
                            ->size(TextColumn\TextColumnSize::Small)
                            ->color('gray'),
                    ]),
                ])->space(2),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'available' => 'Available',
                        'closed' => 'Closed',
                    ])
                    ->indicator('Status'),

                SelectFilter::make('class_id')
                    ->label('Class')
                    ->options(function () {
                        $teacher = static::getCurrentTeacher();
                        if (!$teacher) return [];

                        try {
                            $classes = SchoolClass::where('teacher_id', $teacher->id)->get(['id', 'name']);
                            return static::getSafeOptions($classes, 'name');
                        } catch (\Exception $e) {
                            return [];
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->indicator('Class'),

                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->options(function () {
                        $teacher = static::getCurrentTeacher();
                        if (!$teacher) return [];

                        try {
                            $subjects = Subject::where('teacher_id', $teacher->id)->get(['id', 'name', 'code']);
                            $options = [];
                            foreach ($subjects as $subject) {
                                $name = trim($subject->name ?? '');
                                $code = trim($subject->code ?? '');

                                if (empty($name) && empty($code)) continue;

                                $label = $name ?: $code ?: 'Unnamed Subject';
                                if ($name && $code && $name !== $code) {
                                    $label = "{$name} ({$code})";
                                }

                                $options[$subject->id] = $label;
                            }
                            return $options;
                        } catch (\Exception $e) {
                            return [];
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->indicator('Subject'),

                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(function () {
                        try {
                            $terms = Term::get(['id', 'name']);
                            return static::getSafeOptions($terms, 'name');
                        } catch (\Exception $e) {
                            return [];
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->indicator('Term'),

                Filter::make('overdue')
                    ->label('Overdue Assignments')
                    ->query(fn (Builder $query): Builder => $query->where('deadline', '<', now()))
                    ->indicator('Overdue'),

                Filter::make('recent')
                    ->label('Recent (Last 30 days)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->indicator('Recent'),

                Filter::make('with_submissions')
                    ->label('Has Submissions')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereHas('students', function ($q) {
                            $q->where('assignment_student.status', 'submitted');
                        })
                    )
                    ->indicator('With Submissions'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->iconButton()
                        ->icon('heroicon-m-eye')
                        ->color('info'),

                    Tables\Actions\EditAction::make()
                        ->iconButton()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning'),

                    Action::make('submissions')
                        ->label('View Submissions')
                        ->icon('heroicon-m-clipboard-document-check')
                        ->color('success')
                        ->url(fn (Assignment $record): string =>
                             static::getUrl('view', ['record' => $record->id])
                        )
                        ->iconButton(),

                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('gray')
                        ->action(function (Assignment $record) {
                            $newAssignment = $record->replicate();
                            $newAssignment->title = $record->title . ' (Copy)';
                            $newAssignment->status = 'draft';
                            $newAssignment->deadline = now()->addDays(7);
                            $newAssignment->save();

                            Notification::make()
                                ->title('Assignment Duplicated')
                                ->body('Assignment has been duplicated successfully.')
                                ->success()
                                ->send();
                        })
                        ->iconButton(),
                ])
                ->tooltip('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-m-arrows-right-left')
                        ->form([
                            Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'available' => 'Available',
                                    'closed' => 'Closed',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                                $count++;
                            }

                            static::clearTeacherCache();

                            Notification::make()
                                ->title('Status Updated')
                                ->body("{$count} assignments updated successfully.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No assignments yet')
            ->emptyStateDescription('Create your first assignment to get started with student assessments.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create First Assignment')
                    ->icon('heroicon-m-plus'),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
            'view' => SubmittedStudents::route('/{record}/submissions'),
            'view-submission' => ViewAssignmentSubmission::route('/{assignment}/submissions/{student}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $teacher = static::getCurrentTeacher();

        if (!$teacher) {
            return null;
        }

        try {
            $count = Cache::remember(
                "teacher_{$teacher->id}_pending_assignments",
                300,
                fn() => Assignment::where('teacher_id', $teacher->id)
                    ->where('status', 'draft')
                    ->count()
            );

            return $count > 0 ? (string) $count : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Draft assignments awaiting publication';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title ?: 'Untitled Assignment';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Class' => $record->class?->name ?: 'No class',
            'Subject' => $record->subject?->code ?: $record->subject?->name ?: 'No subject',
            'Due' => $record->deadline?->format('M j, Y') ?: 'No deadline',
        ];
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->url(static::getUrl('view', ['record' => $record])),
        ];
    }

    // Cache management
    public static function clearTeacherCache(): void
    {
        $teacher = static::getCurrentTeacher();

        if ($teacher) {
            Cache::forget("teacher_data_{$teacher->id}");
            Cache::forget("teacher_{$teacher->id}_classes_safe");
            Cache::forget("teacher_{$teacher->id}_subjects_safe");
            Cache::forget("teacher_{$teacher->id}_pending_assignments");
            Cache::forget("assignment_stats_teacher_{$teacher->id}");
        }

        // Clear general caches too
        Cache::forget('active_terms_safe');
        Cache::forget('academic_years_safe');
    }
}
