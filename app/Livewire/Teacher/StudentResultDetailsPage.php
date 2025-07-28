<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\ResultSectionType;
use App\Models\Student;
use App\Models\StudentComment;
use App\Models\Term;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class StudentResultDetailsPage extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Collection $terms;
    public ?int $termId = null;
    public ?int $classId = null;
    public ?int $academic = null;
    public array $courses = [];
    public int|string $record;
    public Collection $academicYears;
    public ?Student $student = null;
    public ?float $total = null;
    public ?int $totalSubject = null;
    public ?float $average = null;
    public ?string $comment = null;
    public ?array $data = [];
    public ?string $errorMessage = null;

    protected $listeners = ['refreshTable' => '$refresh'];

    /**
     * Boot the component and ensure default values are set.
     */
    public function boot(): void
    {
        // The mount method will handle initialization properly
    }

    /**
     * Mount the component and initialize data.
     */
    public function mount($record): void
    {
        // Initialize collections with empty Eloquent collections
        $this->terms = new Collection();
        $this->academicYears = new Collection();

        $this->record = $record;
        $this->loadStudent();
        $this->loadTermsAndYears();
        $this->setDefaultTermAndYear();
        $this->setClassId();

        if ($this->errorMessage) {
            return;
        }

        $this->loadComment();
        $this->calculateTotals();
        $this->getDynamicScoreBoardColumns();

        // Initialize table filters with default values AFTER all defaults are set
        $this->initializeTableFilters();

        Log::info('Mount Debug', [
            'record' => $this->record,
            'termId' => $this->termId,
            'academic' => $this->academic,
            'terms_count' => $this->terms->count(),
            'academic_years_count' => $this->academicYears->count(),
            'initial_filters' => $this->tableFilters,
        ]);
    }

    /**
     * Refresh active term and academic year values.
     */
    private function refreshActiveValues(): void
    {
        // Clear cache to get fresh data
        Cache::forget('active_term_id');
        Cache::forget('active_academic_id');
        Cache::forget('terms_active');
        Cache::forget('academic_years_active');

        // Reload terms and years
        $this->loadTermsAndYears();

        // Set fresh defaults
        $this->setDefaultTermAndYear();

        Log::info('Active values refreshed', [
            'termId' => $this->termId,
            'academic' => $this->academic,
        ]);
    }

    /**
     * Load the student model with optimized query.
     */
    private function loadStudent(): void
    {
        $this->student = Student::select(['id', 'name', 'email', 'class_id', 'group_id'])
            ->with(['class:id,name,class_numeric', 'group:id,name'])
            ->find($this->record);

        if (!$this->student) {
            $this->errorMessage = 'Student not found.';
        }
    }

    /**
     * Load all terms and academic years with caching.
     */
    private function loadTermsAndYears(): void
    {
        // Cache terms and academic years for 10 minutes
        $this->terms = Cache::remember('terms_active', 600, function () {
            return Term::select(['id', 'name', 'status'])
                ->orderBy('name')
                ->get();
        });

        $this->academicYears = Cache::remember('academic_years_active', 600, function () {
            return AcademicYear::select(['id', 'title', 'year', 'status'])
                ->orderBy('starting_date', 'desc')
                ->get();
        });
    }

    /**
     * Set the default term and academic year.
     */
    private function setDefaultTermAndYear(): void
    {
        // Get active term with better error handling
        $this->termId = Cache::remember('active_term_id', 300, function () {
            $activeTerm = Term::where('status', 'true')->first();

            if (!$activeTerm) {
                Log::warning('No active term found, using first available term');
                $activeTerm = Term::orderBy('name')->first();
            }


            Log::info('Active term found', [
                'term_id' => $activeTerm?->id,
                'term_name' => $activeTerm?->name,
                'term_status' => $activeTerm?->status,
            ]);

            return $activeTerm?->id;
        });

        // Get active academic year with better error handling
        $this->academic = Cache::remember('active_academic_id', 300, function () {
            $activeAcademic = AcademicYear::where('status', 'true')->first();

            if (!$activeAcademic) {
                Log::warning('No active academic year found, using first available academic year');
                $activeAcademic = AcademicYear::orderBy('starting_date', 'desc')->first();
            }

            Log::info('Active academic year found', [
                'academic_id' => $activeAcademic?->id,
                'academic_title' => $activeAcademic?->title,
                'academic_status' => $activeAcademic?->status,
            ]);

            return $activeAcademic?->id;
        });

        Log::info('Default Term and Academic Set', [
            'termId' => $this->termId,
            'academic' => $this->academic,
            'terms_available' => $this->terms->count(),
            'academic_years_available' => $this->academicYears->count(),
        ]);
    }

    /**
     * Set the class group ID for the student.
     */
    private function setClassId(): void
    {
        if (!$this->student) {
            return;
        }

        // Use the student's direct group_id first, then fallback to class->group
        $this->classId = $this->student->group_id ?? $this->student->class?->group?->id;

        if (!$this->classId) {
            $this->errorMessage = 'Student class or group is missing. Please assign a group to this student.';
        }

        Log::info('Class ID Set', [
            'classId' => $this->classId,
            'student_group_id' => $this->student->group_id,
            'class_group_id' => $this->student->class?->group?->id,
        ]);
    }

    /**
     * Handle table filter updates.
     */
    public function updatedTableFilters(): void
    {
        $filters = $this->tableFilters;

        Log::info('Filter Update Debug', [
            'all_filters' => $filters,
            'current_termId' => $this->termId,
            'current_academic' => $this->academic,
        ]);

        // Handle term filter
        if (isset($filters['term_filter']['term_id'])) {
            $termValue = $filters['term_filter']['term_id'];
            Log::info('Term filter found', [
                'term_value' => $termValue,
                'current_termId' => $this->termId,
                'will_update' => $termValue !== $this->termId,
            ]);
            if ($termValue !== $this->termId) {
                $this->termId = $termValue;
                Log::info('Updating termId', ['new_termId' => $this->termId]);
                $this->updateTableData();
            }
        } else {
            Log::info('No term_id filter found');
        }

        // Handle academic year filter
        if (isset($filters['academic_filter']['academic_year_id'])) {
            $academicValue = $filters['academic_filter']['academic_year_id'];
            Log::info('Academic filter found', [
                'academic_value' => $academicValue,
                'current_academic' => $this->academic,
                'will_update' => $academicValue !== $this->academic,
            ]);
            if ($academicValue !== $this->academic) {
                $this->academic = $academicValue;
                Log::info('Updating academic', ['new_academic' => $this->academic]);
                $this->updateTableData();
            }
        } else {
            Log::info('No academic_year_id filter found');
        }
    }

    /**
     * Refresh the component and recalculate data.
     */
    public function refreshComponent(): void
    {
        $this->calculateTotals();
        $this->loadComment();
        $this->updateTableData();

        Log::info('Component refreshed', [
            'termId' => $this->termId,
            'academic' => $this->academic,
            'tableFilters' => $this->tableFilters,
        ]);
    }

    /**
     * Public method to refresh active values (called from Blade).
     */
    public function refreshActiveValuesFromUI(): void
    {
        $this->refreshActiveValues();

        // Update table filters with new values
        $this->tableFilters = [
            'term_filter' => [
                'term_id' => $this->termId,
            ],
            'academic_filter' => [
                'academic_year_id' => $this->academic,
            ],
        ];

        $this->updateTableData();

        Log::info('Active values refreshed from UI', [
            'termId' => $this->termId,
            'academic' => $this->academic,
            'tableFilters' => $this->tableFilters,
        ]);
    }

    /**
     * Force refresh the table to show default filter values.
     */
    public function forceTableRefresh(): void
    {
        // Ensure default values are set
        $this->initializeTableFilters();

        // Force a complete table refresh
        $this->dispatch('$refresh');

        Log::info('Table force refreshed', [
            'termId' => $this->termId,
            'academic' => $this->academic,
        ]);
    }

    /**
     * Manually set filter state and force refresh.
     */
    public function setFilterState(): void
    {
        // Ensure we have the correct values
        if (!$this->termId || !$this->academic) {
            $this->setDefaultTermAndYear();
        }

        // Manually set the filter state
        $this->tableFilters = [
            'term_filter' => [
                'term_id' => $this->termId,
            ],
            'academic_filter' => [
                'academic_year_id' => $this->academic,
            ],
        ];

        // Force table refresh
        $this->updateTableData();

        // Dispatch refresh event
        $this->dispatch('$refresh');

        Log::info('Filter state manually set', [
            'termId' => $this->termId,
            'academic' => $this->academic,
            'tableFilters' => $this->tableFilters,
        ]);
    }

    /**
     * Ensure table filters are properly initialized with default values.
     */
    public function initializeTableFilters(): void
    {
        Log::info('Initializing table filters - before check', [
            'termId' => $this->termId,
            'academic' => $this->academic,
        ]);

        if (!$this->termId || !$this->academic) {
            Log::info('Missing termId or academic, calling setDefaultTermAndYear');
            $this->setDefaultTermAndYear();
        }

        Log::info('Setting table filters with values', [
            'termId' => $this->termId,
            'academic' => $this->academic,
        ]);

        // Set the table filters with default values - use the actual values, not null
        $this->tableFilters = [
            'term_filter' => [
                'term_id' => $this->termId,
            ],
            'academic_filter' => [
                'academic_year_id' => $this->academic,
            ],
        ];

        // Force a table refresh to apply the filters
        $this->updateTableData();

        Log::info('Table filters initialized', [
            'termId' => $this->termId,
            'academic' => $this->academic,
            'tableFilters' => $this->tableFilters,
        ]);
    }

    /**
     * Handle term filter change in real-time.
     */
    public function updatedTermId($value): void
    {
        Log::info('Term ID updated', ['new_value' => $value]);

        // Clear the cache for dynamic columns since term changed
        $cacheKey = "result_sections_{$this->termId}_{$this->classId}";
        Cache::forget($cacheKey);

        // Regenerate dynamic columns
        $this->getDynamicScoreBoardColumns();

        // Update table data
        $this->updateTableData();

        Log::info('Term filter updated - columns regenerated', [
            'new_termId' => $value,
            'cache_cleared' => $cacheKey,
        ]);
    }

    /**
     * Handle academic year filter change in real-time.
     */
    public function updatedAcademic($value): void
    {
        Log::info('Academic year updated', ['new_value' => $value]);

        // Clear the cache for dynamic columns since academic year changed
        $cacheKey = "result_sections_{$this->termId}_{$this->classId}";
        Cache::forget($cacheKey);

        // Regenerate dynamic columns
        $this->getDynamicScoreBoardColumns();

        // Update table data
        $this->updateTableData();

        Log::info('Academic filter updated - columns regenerated', [
            'new_academic' => $value,
            'cache_cleared' => $cacheKey,
        ]);
    }

    /**
     * Load the comment for the student, term, and academic year.
     */
    public function loadComment($termId = null): void
    {
        if (!$this->student) {
            $this->comment = null;
            return;
        }

        $termId = $termId ?? $this->termId;
        $academicId = $this->academic;

        if (!$termId || !$academicId) {
            $this->comment = '';
            $this->form->fill(['comment' => '']);
            return;
        }

        $studentComment = StudentComment::where('student_id', $this->student->id)
            ->where('term_id', $termId)
            ->where('academic_id', $academicId)
            ->first();

        $this->comment = $studentComment?->comment ?? '';
        $this->form->fill(['comment' => $this->comment]);

        Log::info('Comment loaded', [
            'student_id' => $this->student->id,
            'term_id' => $termId,
            'academic_id' => $academicId,
            'comment_found' => !empty($this->comment),
        ]);
    }

    /**
     * Define the form schema for comments.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                MarkdownEditor::make('comment')
                    ->label('Teacher Comment')
                    ->placeholder('Enter your comment for this student...'),
            ])
            ->statePath('data');
    }

    /**
     * Save the teacher's comment for the student.
     */
    public function saveComment(): void
    {
        if (!$this->student || !$this->termId || !$this->academic) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Missing required information to save comment.')
                ->send();
            return;
        }

        try {
            $validatedData = $this->form->getState();

            StudentComment::updateOrCreate(
                [
                    'student_id' => $this->student->id,
                    'term_id' => $this->termId,
                    'academic_id' => $this->academic,
                ],
                [
                    'comment' => $validatedData['comment'] ?? '',
                ]
            );

            Notification::make()
                ->title('Comment saved successfully!')
                ->success()
                ->send();

            Log::info('Comment saved', [
                'student_id' => $this->student->id,
                'term_id' => $this->termId,
                'academic_id' => $this->academic,
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving comment', [
                'error' => $e->getMessage(),
                'student_id' => $this->student->id,
            ]);

            Notification::make()
                ->danger()
                ->title('Error saving comment')
                ->body('Please try again.')
                ->send();
        }
    }

    /**
     * Define the Filament table for displaying results.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = CourseForm::with([
                    'subject:id,name,subject_depot_id',
                    'subject.subjectDepot:id,name',
                    'scoreBoard' => function ($query) {
                        $query->select([
                            'id',
                            'course_form_id',
                            'result_section_type_id',
                            'score'
                        ])->with('resultSectionType:id,name,calc_pattern');
                    }
                ])
                    ->where('student_id', $this->record);

                // Apply filters efficiently
                if ($this->termId) {
                    $query->where('term_id', $this->termId);
                }

                if ($this->academic) {
                    $query->where('academic_year_id', $this->academic);
                }

                // Add ordering for consistency
                $query->orderBy('course_forms.id');

                Log::info('Table Query Debug', [
                    'student_id' => $this->record,
                    'termId' => $this->termId,
                    'academic' => $this->academic,
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings(),
                    'count' => $query->count(),
                ]);

                return $query;
            })
            ->columns([
                TextColumn::make('subject.subjectDepot.name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable(),
                ...$this->getDynamicScoreBoardColumns()
            ])
            ->filters([
                // Custom filter components for real-time updates
                \Filament\Tables\Filters\Filter::make('term_filter')
                    ->form([
                        \Filament\Forms\Components\Select::make('term_id')
                            ->label('Term')
                            ->options($this->terms->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->termId = $state;
                                $this->updateTableData();
                            }),
                    ])
                    ->query(function ($query, array $data) {
                        if (isset($data['term_id']) && $data['term_id']) {
                            $query->where('term_id', $data['term_id']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!isset($data['term_id']) || !$data['term_id']) {
                            return null;
                        }
                        $term = $this->terms->find($data['term_id']);
                        return $term ? "Term: {$term->name}" : null;
                    }),

                \Filament\Tables\Filters\Filter::make('academic_filter')
                    ->form([
                        \Filament\Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options($this->academicYears->pluck('title', 'id')->toArray())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->academic = $state;
                                $this->updateTableData();
                            }),
                    ])
                    ->query(function ($query, array $data) {
                        if (isset($data['academic_year_id']) && $data['academic_year_id']) {
                            $query->where('academic_year_id', $data['academic_year_id']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!isset($data['academic_year_id']) || !$data['academic_year_id']) {
                            return null;
                        }
                        $academicYear = $this->academicYears->find($data['academic_year_id']);
                        return $academicYear ? "Academic Year: {$academicYear->title}" : null;
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->filtersFormWidth('md')
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filters')
            )
            ->striped()
            ->paginated(false) // Disable pagination for better performance with small datasets
            ->defaultSort('subject.subjectDepot.name');
    }

    /**
     * Update table data and recalculate totals/comments.
     */
    protected function updateTableData(): void
    {
        try {
            // Clear any cached data
            $this->resetTable();

            // Reload dynamic columns
            $this->getDynamicScoreBoardColumns();

            // Recalculate totals
            $this->calculateTotals();

            // Reload comment for new term/academic year
            $this->loadComment();

            // Dispatch table refresh
            $this->dispatch('refreshTable');

            Log::info('Table data updated successfully', [
                'termId' => $this->termId,
                'academic' => $this->academic,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating table data', [
                'error' => $e->getMessage(),
                'termId' => $this->termId,
                'academic' => $this->academic,
            ]);
        }
    }

    /**
     * Debug method to check filter values.
     */
    public function debugFilters(): void
    {
        $currentFilters = $this->getTableFilters();

        Log::info('Filter Debug', [
            'component_termId' => $this->termId,
            'component_academic' => $this->academic,
            'tableFilters_property' => $this->tableFilters,
            'getTableFilters_method' => $currentFilters,
            'student_id' => $this->record,
            'class_id' => $this->classId,
        ]);

        Notification::make()
            ->title('Debug info logged')
            ->body('Check the logs for filter debug information')
            ->info()
            ->send();
    }

    /**
     * Test method to manually change filters for debugging.
     */
    public function testFilterChange(): void
    {
        if ($this->terms->count() > 1) {
            $newTermId = $this->terms->where('id', '!=', $this->termId)->first()?->id;
            if ($newTermId) {
                Log::info('Testing term change', [
                    'old_termId' => $this->termId,
                    'new_termId' => $newTermId,
                ]);
                $this->termId = $newTermId;
                $this->updateTableData();

                Notification::make()
                    ->title('Test: Term changed')
                    ->body("Changed to term ID: {$newTermId}")
                    ->info()
                    ->send();
            }
        }
    }

    /**
     * Calculate total, subject count, and average for the student - OPTIMIZED.
     */
    public function calculateTotals(): void
    {
        if (!$this->student || !$this->termId || !$this->academic) {
            $this->total = $this->average = null;
            $this->totalSubject = 0;
            return;
        }

        // Use optimized query with joins
        $results = DB::table('course_forms')
            ->join('result_section_student_types', 'course_forms.id', '=', 'result_section_student_types.course_form_id')
            ->join('result_section_types', 'result_section_student_types.result_section_type_id', '=', 'result_section_types.id')
            ->where('course_forms.student_id', $this->record)
            ->where('course_forms.term_id', $this->termId)
            ->where('course_forms.academic_year_id', $this->academic)
            ->where('result_section_types.calc_pattern', 'total')
            ->select([
                'course_forms.id as course_form_id',
                DB::raw('CAST(result_section_student_types.score AS DECIMAL(10,2)) as score')
            ])
            ->get();

        $this->total = $results->sum('score');
        $this->totalSubject = $results->count();
        $this->average = $this->totalSubject > 0 ? round($this->total / $this->totalSubject, 2) : 0.0;

        Log::info('Totals calculated', [
            'total' => $this->total,
            'totalSubject' => $this->totalSubject,
            'average' => $this->average,
            'results_count' => $results->count(),
        ]);
    }

    /**
     * Get the remarks statement for a given total.
     */
    protected function remarksStatement(float $total): string
    {
        return match (true) {
            $total >= 80 => 'EXCELLENT',
            $total >= 70 => 'VERY GOOD',
            $total >= 65 => 'GOOD',
            $total >= 61 => 'CREDIT',
            $total >= 55 => 'CREDIT',
            $total >= 50 => 'CREDIT',
            $total >= 45 => 'PASS',
            $total >= 39 => 'FAIL',
            default => 'FAIL'
        };
    }

    /**
     * Dynamically generate columns for the result table based on section types - OPTIMIZED.
     */
    protected function getDynamicScoreBoardColumns(): array
    {
        Log::info('getDynamicScoreBoardColumns called', [
            'termId' => $this->termId,
            'classId' => $this->classId,
            'has_termId' => !empty($this->termId),
            'has_classId' => !empty($this->classId),
        ]);

        if (!$this->termId || !$this->classId) {
            Log::warning('Missing termId or classId for dynamic columns', [
                'termId' => $this->termId,
                'classId' => $this->classId,
            ]);
            return [];
        }

        // Cache the dynamic fields for 5 minutes
        $cacheKey = "result_sections_{$this->termId}_{$this->classId}";

        $dynamicFields = ResultSectionType::select(['id', 'name', 'code', 'calc_pattern', 'type', 'score_weight'])
            ->where('term_id', $this->termId)
            ->whereHas('resultSection', function ($query) {
                $query->where('group_id', $this->classId);
            })
            ->orderBy('name')
            ->get();

        Log::info('Dynamic fields fetched from database', [
            'term_id' => $this->termId,
            'class_id' => $this->classId,
            'fields_count' => $dynamicFields->count(),
            'fields' => $dynamicFields->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'code' => $f->code, 'calc_pattern' => $f->calc_pattern])->toArray(),
        ]);

        $columns = $dynamicFields->map(function ($field) {
            // Create a better label that includes both name and code
            $label = $field->code ? "{$field->name} ({$field->code})" : $field->name;

            return TextColumn::make("scoreBoard.{$field->id}")
                ->label($label)
                ->description($field->code) // Add code as description for tooltip
                ->state(function ($record) use ($field) {
                    $scoreItem = $record->scoreBoard
                        ->where('result_section_type_id', $field->id)
                        ->first();

                    if (!$scoreItem) {
                        return 'N/A';
                    }

                    $score = $scoreItem->score;
                    return is_numeric($score) ? number_format((float)$score, 1) : ($score ?? 'N/A');
                })
                ->alignCenter()
                ->sortable()
                ->tooltip($field->name) // Show full name on hover
                ->badge()
                ->color(function ($state) use ($field) {
                    // Color coding based on calc_pattern
                    return match ($field->calc_pattern) {
                        'input' => 'primary',
                        'total' => 'success',
                        'position' => 'warning',
                        'grade_level' => 'info',
                        'class_average' => 'secondary',
                        'class_highest_score' => 'success',
                        'class_lowest_score' => 'danger',
                        'remarks' => 'gray',
                        default => 'gray'
                    };
                });
        })->toArray();

        Log::info('Dynamic columns generated', [
            'fields_count' => $dynamicFields->count(),
            'columns_count' => count($columns),
            'cache_key' => $cacheKey,
            'fields' => $dynamicFields->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'code' => $f->code, 'calc_pattern' => $f->calc_pattern])->toArray(),
        ]);

        return $columns;
    }

    /**
     * Get scoreboard structure information for display.
     */
    public function getScoreboardStructure(): array
    {
        if (!$this->termId || !$this->classId) {
            return [];
        }

        $cacheKey = "scoreboard_structure_{$this->termId}_{$this->classId}";

        return Cache::remember($cacheKey, 300, function () {
            try {
                $resultSections = ResultSectionType::select(['id', 'name', 'code', 'calc_pattern', 'type'])
                    ->where('term_id', $this->termId)
                    ->whereHas('resultSection', function ($query) {
                        $query->where('group_id', $this->classId);
                    })
                    ->orderBy('name')
                    ->get();

                // Filter out records with null calc_pattern to avoid groupBy issues
                $validSections = $resultSections->filter(function ($section) {
                    return !empty($section->calc_pattern);
                });

                $structure = $validSections->groupBy('calc_pattern');

                return [
                    'input_sections' => $structure->has('input') ? $structure->get('input')->map(fn($s) => ['name' => $s->name, 'code' => $s->code]) : collect(),
                    'total_sections' => $structure->has('total') ? $structure->get('total')->map(fn($s) => ['name' => $s->name, 'code' => $s->code]) : collect(),
                    'calculated_sections' => $structure->except(['input', 'total'])->flatten(1)->map(fn($s) => ['name' => $s->name, 'code' => $s->code, 'calc_pattern' => $s->calc_pattern]),
                    'all_sections' => $structure->flatten(1)->map(fn($s) => ['name' => $s->name, 'code' => $s->code, 'calc_pattern' => $s->calc_pattern]),
                ];
            } catch (\Exception $e) {
                Log::error('Error in getScoreboardStructure', [
                    'error' => $e->getMessage(),
                    'termId' => $this->termId,
                    'classId' => $this->classId,
                ]);

                return [
                    'input_sections' => collect(),
                    'total_sections' => collect(),
                    'calculated_sections' => collect(),
                    'all_sections' => collect(),
                ];
            }
        });
    }

    /**
     * Render the Livewire component view.
     */
    public function render()
    {
        return view('livewire.teacher.student-result-details-page', [
            'errorMessage' => $this->errorMessage,
            'total' => $this->total,
            'average' => $this->average,
            'totalSubject' => $this->totalSubject,
            'remarks' => $this->total ? $this->remarksStatement($this->total) : null,
            'scoreboardStructure' => $this->getScoreboardStructure(),
        ]);
    }
}
