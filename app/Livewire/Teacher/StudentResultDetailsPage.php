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

    protected $listeners = ['refreshTable' => '$refresh'];

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
        // Simplified approach - actual filter handling is done in updatedTermId() and updatedAcademic()
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
            // Use the comment property directly
            $commentText = $this->comment ?? '';

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

            Notification::make()
                ->title('Comment saved successfully!')
                ->body("Comment saved for {$this->student->name}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error saving comment')
                ->body('Error: ' . $e->getMessage())
                ->send();
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
                    ->live(false)
                    ->afterStateUpdated(function ($state) {
                        // Store the selected value but don't apply immediately
                        $this->selectedTermId = $state;
                    }),

                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options($this->academicYears->pluck('title', 'id')->toArray())
                    ->default($this->academic)
                    ->searchable()
                    ->live(false)
                    ->afterStateUpdated(function ($state) {
                        // Store the selected value but don't apply immediately
                        $this->selectedAcademicId = $state;
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->filtersFormWidth('md')
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Apply Filters')
                    ->color('primary')
                    ->icon('heroicon-o-funnel')
                    ->action('applyFilters')
            )
            ->filtersFormActions([
                Action::make('apply')
                    ->label('Apply Filters')
                    ->color('primary')
                    ->icon('heroicon-o-funnel')
                    ->action('applyFilters'),
                Action::make('reset')
                    ->label('Reset Filters')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-path')
                    ->action('resetFilters'),
            ])
            ->striped()
            ->paginated(false)
            ->defaultSort('subject.subjectDepot.name')
            ->recordUrl(null) // Prevent duplicate navigation
            ->recordAction(null) // Remove any default record actions
            ->deferFilters() // Defer filter loading to prevent multiple renders
            ->persistFiltersInSession() // Persist filters to avoid reloading
            ->extremePaginationLinks(); // Simplify pagination
    }

        public function applyFilters(): void
    {
        // Apply the selected filter values
        if ($this->selectedTermId !== null) {
            $this->termId = $this->selectedTermId;
        }

        if ($this->selectedAcademicId !== null) {
            $this->academic = $this->selectedAcademicId;
        }

        // Update the table data
        $this->updateTableData();

        // Show success notification
        Notification::make()
            ->success()
            ->title('Filters Applied')
            ->body('Results have been filtered successfully.')
            ->send();
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
            $this->dispatch('refreshTable');
        } catch (\Exception $e) {
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
    }

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
            'remarks' => $this->total ? $this->remarksStatement($this->total) : null,
            'scoreboardStructure' => $this->getScoreboardStructure(),
        ]);
    }
}
