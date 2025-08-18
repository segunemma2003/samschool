<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\ResultSectionType;
use App\Models\Student;
use App\Models\StudentComment;
use App\Models\StudentResult;
use App\Services\StudentResultCalculationService;
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
    public ?int $selectedTermId = null;
    public ?int $selectedAcademicId = null;
    public bool $isTableLoading = false;

    protected $listeners = [
        'refreshTable' => '$refresh',
        'table-loading-started' => 'startTableLoading',
        'table-loading-finished' => 'finishTableLoading'
    ];

    public function startTableLoading(): void
    {
        $this->isTableLoading = true;
    }

    public function finishTableLoading(): void
    {
        $this->isTableLoading = false;
    }

    public function getId()
    {
        return 'student-result-details-' . $this->record;
    }

    // Prevent multiple instances
    public static $instances = [];

    public function dehydrate()
    {
        // Clear instance tracking on dehydrate
        static::$instances = [];
    }

    public function boot(): void
    {
        // The mount method will handle initialization properly
    }

    public function mount($record): void
    {
        // Prevent multiple instances of the same record
        $instanceKey = "student_result_{$record}";
        if (isset(static::$instances[$instanceKey])) {
            return;
        }
        static::$instances[$instanceKey] = true;

        $this->terms = new Collection();
        $this->academicYears = new Collection();

        $this->record = $record;
        $this->loadStudent();
        $this->loadTermsAndYears();
        $this->setDefaultTermAndYear();
        $this->setClassId();

        // Initialize selected filter values
        $this->selectedTermId = $this->termId;
        $this->selectedAcademicId = $this->academic;

        if ($this->errorMessage) {
            return;
        }

        $this->loadComment();
        $this->calculateTotals();
        $this->getDynamicScoreBoardColumns();
    }

    private function refreshActiveValues(): void
    {
        Cache::forget('active_term_id');
        Cache::forget('active_academic_id');
        Cache::forget('terms_active');
        Cache::forget('academic_years_active');

        $this->loadTermsAndYears();
        $this->setDefaultTermAndYear();
    }

    private function loadStudent(): void
    {
        $this->student = Student::select(['id', 'name', 'email', 'class_id', 'group_id'])
            ->with(['class:id,name,class_numeric', 'group:id,name'])
            ->find($this->record);

        if (!$this->student) {
            $this->errorMessage = 'Student not found.';
        }
    }

    private function loadTermsAndYears(): void
    {
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

    private function setDefaultTermAndYear(): void
    {
        $this->termId = Cache::remember('active_term_id', 300, function () {
            $activeTerm = Term::where('status', 'true')->first();

            if (!$activeTerm) {
                $activeTerm = Term::orderBy('name')->first();
            }

            return $activeTerm?->id;
        });

        $this->academic = Cache::remember('active_academic_id', 300, function () {
            $activeAcademic = AcademicYear::where('status', 'true')->first();

            if (!$activeAcademic) {
                $activeAcademic = AcademicYear::orderBy('starting_date', 'desc')->first();
            }

            return $activeAcademic?->id;
        });
    }

    private function setClassId(): void
    {
        if (!$this->student) {
            return;
        }

        $this->classId = $this->student->group_id ?? $this->student->class?->group?->id;

        if (!$this->classId) {
            $this->errorMessage = 'Student class or group is missing. Please assign a group to this student.';
        }
    }

    public function updatedTableFilters(): void
    {
        // Don't automatically apply filters - let user click apply button
        // This keeps the filter button selectable
    }

    public function applyTableFilters(): void
    {
        // This method is called when filters are applied through the table
        $this->applyFilters();
    }

    public function onTableFilterApplied(): void
    {
        // This method is called when table filters are applied
        $this->applyFilters();
    }

    public function loadComment($termId = null): void
    {
        if (!$this->student) {
            $this->comment = '';
            return;
        }

        $termId = $termId ?? $this->termId;
        $academicId = $this->academic;

        if (!$termId || !$academicId) {
            $this->comment = '';
            return;
        }

        $studentComment = StudentComment::where('student_id', $this->student->id)
            ->where('term_id', $termId)
            ->where('academic_id', $academicId)
            ->first();

        $this->comment = $studentComment?->comment ?? '';
    }

    public function updatedData($value, $key): void
    {
        // Update the comment property when form data changes
        if ($key === 'comment') {
            $this->comment = $value;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                MarkdownEditor::make('comment')
                    ->label('Teacher Comment')
                    ->placeholder('Enter your comment for this student...')
                    ->default($this->comment ?? '')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->comment = $state;
                    }),
            ]);
    }

    protected function calculateSubjectGrade(float $score): string
    {
        return match (true) {
            $score >= 75 => 'A1',
            $score >= 70 => 'B2',
            $score >= 65 => 'B3',
            $score >= 60 => 'C4',
            $score >= 55 => 'C5',
            $score >= 50 => 'C6',
            $score >= 45 => 'D7',
            $score >= 40 => 'E8',
            default => 'F9'
        };
    }

    protected function calculateOverallGrade(float $average): string
    {
        return $this->calculateSubjectGrade($average);
    }

    public function saveComment(): void
    {
        // Simple test to verify method is called
        Notification::make()
            ->title('Debug: Method Called')
            ->body("Student: {$this->student?->name}, Term: {$this->termId}, Academic: {$this->academic}, ClassId: {$this->classId}")
            ->info()
            ->send();

        if (!$this->student || !$this->termId || !$this->academic) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Missing required information to save comment.')
                ->send();
            return;
        }

        try {
            // Use the comment property directly
            $commentText = $this->comment ?? '';

            // Debug logging
            Log::info('Starting saveComment process', [
                'student_id' => $this->student->id,
                'term_id' => $this->termId,
                'academic_id' => $this->academic,
                'comment' => $commentText
            ]);

            // Create or update the comment
            $studentComment = StudentComment::updateOrCreate(
                [
                    'student_id' => $this->student->id,
                    'term_id' => $this->termId,
                    'academic_id' => $this->academic,
                ],
                [
                    'comment' => $commentText,
                ]
            );

            Log::info('StudentComment saved', ['comment_id' => $studentComment->id]);

            // Get the exact same data that's displayed in the view result page
            $this->calculateTotals(); // Ensure totals are calculated

            Log::info('Totals calculated', [
                'total' => $this->total,
                'average' => $this->average,
                'totalSubject' => $this->totalSubject
            ]);

            // Get course forms with scores (same as view result page)
            $courseForms = CourseForm::where('student_id', $this->record)
                ->where('term_id', $this->termId)
                ->where('academic_year_id', $this->academic)
                ->with(['scoreBoard.resultSectionType', 'subject.subjectDepot', 'subject.teacher'])
                ->get();

            Log::info('Course forms retrieved', ['count' => $courseForms->count()]);

            // Get result section types (same as view result page)
            $resultSectionTypes = ResultSectionType::select(['id', 'name', 'code', 'calc_pattern', 'type', 'score_weight'])
                ->where('term_id', $this->termId)
                ->whereHas('resultSection', function ($query) {
                    $query->where('group_id', $this->classId);
                })
                ->orderBy('name')
                ->get();

            Log::info('Result section types retrieved', ['count' => $resultSectionTypes->count()]);

            // Calculate class metrics for position and class averages
            $classMetrics = $this->calculateClassMetrics($this->student->class_id, $this->termId, $this->academic);

            // Build subjects data exactly as shown in view result page
            $subjects = [];
            $uniqueSubjects = $courseForms->unique('subject_id');

            foreach ($uniqueSubjects as $courseForm) {
                $subjectScores = [];
                $subjectTotal = 0;
                $caScore = 0;
                $examScore = 0;

                // Get all scores for this subject
                foreach ($courseForm->scoreBoard as $score) {
                    $sectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                    if ($sectionType) {
                        $scoreValue = is_numeric($score->score) ? (float)$score->score : 0;
                        $subjectScores[] = [
                            'type' => $sectionType->name,
                            'code' => $sectionType->code,
                            'calc_pattern' => $sectionType->calc_pattern,
                            'score' => $scoreValue
                        ];

                        // Calculate CA and Exam scores from individual scores (same as PDF template)
                        if (stripos($sectionType->name ?? '', 'ca') !== false ||
                            stripos($sectionType->name ?? '', 'test') !== false ||
                            stripos($sectionType->name ?? '', 'assignment') !== false) {
                            $caScore += $scoreValue;
                        } elseif (stripos($sectionType->name ?? '', 'exam') !== false) {
                            $examScore += $scoreValue;
                        }

                        // Calculate total using same logic as view result page
                        if ($sectionType->calc_pattern === 'total') {
                            $subjectTotal += $scoreValue;
                        }
                    }
                }

                // If no specific breakdown, assume 40/60 split (same as PDF template)
                if ($caScore == 0 && $examScore == 0 && $subjectTotal > 0) {
                    $caScore = round($subjectTotal * 0.4);
                    $examScore = round($subjectTotal * 0.6);
                }

                // Get class metrics for this subject
                $subjectClassMetrics = $classMetrics['subjects'][$courseForm->subject_id] ?? [];

                $subjects[] = [
                    'subject_id' => $courseForm->subject_id,
                    'subject_name' => $courseForm->subject->subjectDepot->name ?? 'Unknown Subject',
                    'subject_code' => $courseForm->subject->code ?? '',
                    'scores' => $subjectScores,
                    'ca_score' => $caScore,
                    'exam_score' => $examScore,
                    'total' => $subjectTotal,
                    'grade' => $this->calculateSubjectGrade($subjectTotal),
                    'position' => $subjectClassMetrics['position'] ?? 'N/A',
                    'class_average' => $subjectClassMetrics['class_average'] ?? 'N/A',
                    'highest_score' => $subjectClassMetrics['highest_score'] ?? 'N/A',
                    'lowest_score' => $subjectClassMetrics['lowest_score'] ?? 'N/A',
                    'teacher_name' => $courseForm->subject->teacher->name ?? 'TEACHER'
                ];
            }

            Log::info('Subjects data built', ['subjects_count' => count($subjects)]);

            // Calculate summary using same logic as view result page
            $summary = [
                'total_score' => $this->total ?? 0,
                'total_subjects' => $this->totalSubject ?? 0,
                'average' => $this->average ?? 0,
                'grade' => $this->calculateOverallGrade($this->average ?? 0),
                'remarks' => $this->remarksStatement($this->average ?? 0),
                'position' => $classMetrics['student_position'] ?? 'N/A',
                'total_students' => $classMetrics['total_students'] ?? 0
            ];

            Log::info('Summary calculated', $summary);

            // Prepare the complete JSON data
            $jsonData = [
                'subjects' => $subjects,
                'summary' => $summary,
                'metadata' => [
                    'calculated_at' => now()->toISOString(),
                    'calculated_by' => 'teacher_comment',
                    'student_id' => $this->student->id,
                    'term_id' => $this->termId,
                    'academic_year_id' => $this->academic,
                    'view_result_data' => true, // Flag to indicate this is from view result page
                    'show_position' => $this->getSchoolPositionSetting() // Include position setting
                ]
            ];

            Log::info('JSON data prepared', ['data_size' => strlen(json_encode($jsonData))]);

            // Create or update the student result with exact view result data
            $studentResult = StudentResult::updateOrCreate(
                [
                    'student_id' => $this->student->id,
                    'term_id' => $this->termId,
                    'academic_year_id' => $this->academic,
                ],
                [
                    'class_id' => $this->student->class_id,
                    'total_score' => $summary['total_score'],
                    'average_score' => $summary['average'],
                    'grade' => $summary['grade'],
                    'remarks' => $summary['remarks'],
                    'total_subjects' => $summary['total_subjects'],
                    'teacher_comment' => $commentText,
                    'commented_by' => \Filament\Facades\Filament::auth()->id(),
                    'calculated_data' => $jsonData,
                    'calculated_at' => now(),
                    'calculation_status' => 'completed',
                ]
            );

            Log::info('StudentResult saved', [
                'result_id' => $studentResult->id,
                'wasRecentlyCreated' => $studentResult->wasRecentlyCreated
            ]);

            Notification::make()
                ->title('Comment and Results saved successfully!')
                ->body("Comment and calculated results saved for {$this->student->name}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Error in saveComment', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->danger()
                ->title('Error saving comment and results')
                ->body('Error: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Calculate class metrics for position and class averages
     */
    private function calculateClassMetrics(int $classId, int $termId, int $academicId): array
    {
        try {
            // Get all students in the same class
            $classStudents = Student::where('class_id', $classId)->pluck('id')->toArray();

            if (empty($classStudents)) {
                return [
                    'student_position' => 'N/A',
                    'total_students' => 0,
                    'subjects' => []
                ];
            }

            // Get result section types for this term and class
            $resultSectionTypes = ResultSectionType::select(['id', 'name', 'code', 'calc_pattern'])
                ->where('term_id', $termId)
                ->whereHas('resultSection', function ($query) use ($classId) {
                    $query->where('group_id', $classId);
                })
                ->orderBy('name')
                ->get();

            // Get all course forms for the class
            $classCourseForms = CourseForm::whereIn('student_id', $classStudents)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicId)
                ->with(['scoreBoard.resultSectionType', 'subject'])
                ->get();

            // Calculate student totals for overall position
            $studentTotals = [];
            foreach ($classStudents as $studentId) {
                $studentCourseForms = $classCourseForms->where('student_id', $studentId);
                $studentTotal = 0;
                $subjectCount = 0;

                foreach ($studentCourseForms->unique('subject_id') as $courseForm) {
                    $subjectTotal = 0;
                    foreach ($courseForm->scoreBoard as $score) {
                        $sectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                        if ($sectionType && $sectionType->calc_pattern === 'total') {
                            $subjectTotal += (float)$score->score;
                        }
                    }
                    if ($subjectTotal > 0) {
                        $studentTotal += $subjectTotal;
                        $subjectCount++;
                    }
                }

                if ($subjectCount > 0) {
                    $studentTotals[$studentId] = $studentTotal;
                }
            }

            // Calculate overall position
            arsort($studentTotals);
            $positions = [];
            $rank = 1;
            $previousScore = null;
            $tieCount = 0;

            foreach ($studentTotals as $studentId => $score) {
                if ($score !== $previousScore) {
                    $rank += $tieCount;
                    $tieCount = 0;
                } else {
                    $tieCount++;
                }
                $positions[$studentId] = $rank;
                $previousScore = $score;
            }

            // Calculate subject-specific metrics
            $subjectMetrics = [];
            $uniqueSubjects = $classCourseForms->unique('subject_id');

            foreach ($uniqueSubjects as $courseForm) {
                $subjectId = $courseForm->subject_id;
                $subjectScores = [];

                // Get all scores for this subject across all students
                foreach ($classCourseForms->where('subject_id', $subjectId) as $cf) {
                    $subjectTotal = 0;
                    foreach ($cf->scoreBoard as $score) {
                        $sectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                        if ($sectionType && $sectionType->calc_pattern === 'total') {
                            $subjectTotal += (float)$score->score;
                        }
                    }
                    if ($subjectTotal > 0) {
                        $subjectScores[$cf->student_id] = $subjectTotal;
                    }
                }

                if (!empty($subjectScores)) {
                    // Calculate subject position for current student
                    arsort($subjectScores);
                    $subjectPositions = [];
                    $rank = 1;
                    $previousScore = null;
                    $tieCount = 0;

                    foreach ($subjectScores as $studentId => $score) {
                        if ($score !== $previousScore) {
                            $rank += $tieCount;
                            $tieCount = 0;
                        } else {
                            $tieCount++;
                        }
                        $subjectPositions[$studentId] = $rank;
                        $previousScore = $score;
                    }

                    $subjectMetrics[$subjectId] = [
                        'position' => $subjectPositions[$this->student->id] ?? 'N/A',
                        'class_average' => round(array_sum($subjectScores) / count($subjectScores), 1),
                        'highest_score' => max($subjectScores),
                        'lowest_score' => min($subjectScores)
                    ];
                }
            }

            return [
                'student_position' => $this->formatPosition($positions[$this->student->id] ?? 'N/A'),
                'total_students' => count($classStudents),
                'subjects' => $subjectMetrics
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating class metrics', [
                'error' => $e->getMessage(),
                'class_id' => $classId,
                'term_id' => $termId,
                'academic_id' => $academicId
            ]);

            return [
                'student_position' => 'N/A',
                'total_students' => 0,
                'subjects' => []
            ];
        }
    }

    /**
     * Format position with proper suffix
     */
    private function formatPosition($position): string
    {
        if (!is_numeric($position)) {
            return 'N/A';
        }

        $suffix = match ($position % 10) {
            1 => $position % 100 === 11 ? 'TH' : 'ST',
            2 => $position % 100 === 12 ? 'TH' : 'ND',
            3 => $position % 100 === 13 ? 'TH' : 'RD',
            default => 'TH'
        };

        return $position . $suffix;
    }

    /**
     * Get school position setting
     */
    private function getSchoolPositionSetting(): bool
    {
        try {
            $schoolInfo = \App\Models\SchoolInformation::where([
                ['term_id', $this->termId],
                ['academic_id', $this->academic]
            ])->first();

            return $schoolInfo && $schoolInfo->activate_position === 'yes';
        } catch (\Exception $e) {
            Log::error('Error getting school position setting', [
                'error' => $e->getMessage(),
                'term_id' => $this->termId,
                'academic_id' => $this->academic
            ]);
            return false;
        }
    }

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

                if ($this->termId) {
                    $query->where('term_id', $this->termId);
                }

                if ($this->academic) {
                    $query->where('academic_year_id', $this->academic);
                }

                $query->orderBy('course_forms.id');

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
                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options($this->terms->pluck('name', 'id')->toArray())
                    ->default($this->termId)
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (isset($data['value']) && $data['value']) {
                            $this->termId = (int) $data['value'];
                            $this->selectedTermId = $this->termId;
                            $query->where('term_id', $data['value']);

                            // Recalculate totals immediately
                            $this->calculateTotals();
                            $this->loadComment();
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (isset($data['value']) && $data['value']) {
                            $term = $this->terms->find($data['value']);
                            return $term ? $term->name : null;
                        }
                        return null;
                    }),

                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options($this->academicYears->pluck('title', 'id')->toArray())
                    ->default($this->academic)
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (isset($data['value']) && $data['value']) {
                            $this->academic = (int) $data['value'];
                            $this->selectedAcademicId = $this->academic;
                            $query->where('academic_year_id', $data['value']);

                            // Recalculate totals immediately
                            $this->calculateTotals();
                            $this->loadComment();
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (isset($data['value']) && $data['value']) {
                            $year = $this->academicYears->find($data['value']);
                            return $year ? $year->title : null;
                        }
                        return null;
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->filtersFormWidth('md')
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filters')
                    ->color('primary')
                    ->icon('heroicon-o-funnel')
            )
            ->striped()
            ->paginated(false)
            ->defaultSort('subject.subjectDepot.name')
            ->recordUrl(null) // Prevent duplicate navigation
            ->recordAction(null) // Remove any default record actions
            ->deferFilters(false) // Apply filters immediately when selected
            ->persistFiltersInSession() // Persist filters to avoid reloading
            ->extremePaginationLinks(); // Simplify pagination
    }

    public function applyFilters(): void
    {
        // Get the current filter values from the table
        $tableFilters = $this->getTableFilters();

        // Update the component properties based on filter values
        if (isset($tableFilters['term_id']['value'])) {
            $this->termId = (int) $tableFilters['term_id']['value'];
            $this->selectedTermId = $this->termId;
        }

        if (isset($tableFilters['academic_year_id']['value'])) {
            $this->academic = (int) $tableFilters['academic_year_id']['value'];
            $this->selectedAcademicId = $this->academic;
        }

        // Recalculate totals with new filter values
        $this->calculateTotals();
        $this->loadComment();

        // Show success notification
        Notification::make()
            ->success()
            ->title('Filters Applied')
            ->body('Results have been filtered successfully.')
            ->send();
    }

    public function applyFiltersImmediately(): void
    {
        // This method can be called to apply filters immediately when selected
        $this->applyFilters();
    }

    public function resetFilters(): void
    {
        // Reset to default values
        $this->setDefaultTermAndYear();
        $this->selectedTermId = $this->termId;
        $this->selectedAcademicId = $this->academic;

        // Update the table data
        $this->updateTableData();

        // Show success notification
        Notification::make()
            ->success()
            ->title('Filters Reset')
            ->body('Filters have been reset to default values.')
            ->send();
    }

    protected function updateTableData(): void
    {
        try {
            $this->resetTable();
            $this->getDynamicScoreBoardColumns();
            $this->calculateTotals();
            $this->loadComment();

            // Stop loading after data is updated
            $this->isTableLoading = false;
            $this->dispatch('table-loading-finished');
        } catch (\Exception $e) {
            $this->isTableLoading = false;
            $this->dispatch('table-loading-finished');
            // Handle error silently or show notification if needed
        }
    }

    public function calculateTotals(): void
    {
        if (!$this->student || !$this->termId || !$this->academic) {
            $this->total = $this->average = null;
            $this->totalSubject = 0;
            return;
        }

        // Get course forms for this student, term, and academic year
        $courseForms = CourseForm::where('student_id', $this->record)
            ->where('term_id', $this->termId)
            ->where('academic_year_id', $this->academic)
            ->with(['scoreBoard.resultSectionType'])
            ->get();

        // Count unique subjects
        $uniqueSubjects = $courseForms->unique('subject_id');
        $this->totalSubject = $uniqueSubjects->count();

        if ($this->totalSubject === 0) {
            $this->total = $this->average = 0;
            return;
        }

        // Calculate total and average per subject
        $totalScore = 0;
        $subjectsWithScores = 0;

        foreach ($uniqueSubjects as $courseForm) {
            $subjectTotal = 0;
            $scoreCount = 0;

            foreach ($courseForm->scoreBoard as $score) {
                if ($score->resultSectionType && $score->resultSectionType->calc_pattern === 'total') {
                    $subjectTotal += (float) $score->score;
                    $scoreCount++;
                }
            }

            // Use the total score for this subject (should be only one total per subject)
            if ($scoreCount > 0) {
                $totalScore += $subjectTotal; // Use total directly, not average
                $subjectsWithScores++;
            }
        }

        $this->total = round($totalScore, 2);
        // Use subjects with scores for average calculation
        $this->average = $subjectsWithScores > 0 ? round($totalScore / $subjectsWithScores, 2) : 0.0;
    }

    protected function remarksStatement(float $total): string
    {
        return match (true) {
            $total >= 86 => 'EXCELLENT',
            $total >= 75 => 'VERY GOOD',
            $total >= 65 => 'GOOD',
            $total >= 51 => 'WORK HARDER',
            $total >= 41 => 'PUT IN MORE EFFORT',
            default => 'BE MORE SERIOUS'
        };
    }

    protected function getDynamicScoreBoardColumns(): array
    {
        if (!$this->termId || !$this->classId) {
            return [];
        }

        $dynamicFields = ResultSectionType::select(['id', 'name', 'code', 'calc_pattern', 'type', 'score_weight'])
            ->where('term_id', $this->termId)
            ->whereHas('resultSection', function ($query) {
                $query->where('group_id', $this->classId);
            })
            ->orderBy('name')
            ->get();

        $columns = $dynamicFields->map(function ($field) {
            $label = $field->code ? "{$field->name} ({$field->code})" : $field->name;

            return TextColumn::make("scoreBoard.{$field->id}")
                ->label($label)
                ->description($field->code)
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
                ->tooltip($field->name)
                ->badge()
                ->color(function ($state) use ($field) {
                    return match ($field->calc_pattern) {
                        'input' => 'primary',
                        'total' => 'success',
                        'cumulative' => 'info',
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

        return $columns;
    }

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
                return [
                    'input_sections' => collect(),
                    'total_sections' => collect(),
                    'calculated_sections' => collect(),
                    'all_sections' => collect(),
                ];
            }
        });
    }

    public function render()
    {
        return view('livewire.teacher.student-result-details-page', [
            'errorMessage' => $this->errorMessage,
            'total' => $this->total,
            'average' => $this->average,
            'totalSubject' => $this->totalSubject,
            'remarks' => $this->average ? $this->remarksStatement($this->average) : null,
            'scoreboardStructure' => $this->getScoreboardStructure(),
        ]);
    }
}
