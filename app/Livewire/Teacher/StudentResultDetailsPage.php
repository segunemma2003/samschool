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
     * Mount the component and initialize data.
     */
    public function mount($record): void
    {
        $this->record = $record;
        $this->loadStudent();
        $this->loadTermsAndYears();
        $this->setDefaultTermAndYear();
        $this->setClassId();
        if ($this->errorMessage) {
            return;
        }
        $this->loadComment();
        $this->getDynamicScoreBoardColumns();
    }

    /**
     * Load the student model.
     */
    private function loadStudent(): void
    {
        $this->student = Student::find($this->record);
        if (! $this->student) {
            $this->errorMessage = 'Student not found.';
        }
    }

    /**
     * Load all terms and academic years.
     */
    private function loadTermsAndYears(): void
    {
        $this->terms = Term::all();
        $this->academicYears = AcademicYear::all();
    }

    /**
     * Set the default term and academic year.
     */
    private function setDefaultTermAndYear(): void
    {
        $this->termId = Term::where('status', "true")->first()?->id;
        $this->academic = AcademicYear::where('status', "true")->first()?->id;
    }

    /**
     * Set the class group ID for the student.
     */
    private function setClassId(): void
    {
        if (! $this->student) {
            return;
        }
        if ($this->student->class && $this->student->class->group) {
            $this->classId = $this->student->class->group->id;
        } else {
            $this->errorMessage = 'Student class or group is missing.';
        }
    }

    /**
     * Handle table filter updates.
     */
    public function updatedTableFilters(): void
    {
        $filters = $this->tableFilters;
        if (isset($filters['term_id'])) {
            $this->termId = $filters['term_id']['value'];
            $this->updateTableData();
        }
        if (isset($filters['academic_year_id'])) {
            $this->academic = $filters['academic_year_id']['value'];
            $this->updateTableData();
        }
    }

    /**
     * Load the comment for the student, term, and academic year.
     */
    public function loadComment($termId = null): void
    {
        if (! $this->student) {
            $this->comment = null;
            return;
        }
        $termId = $termId ?? $this->termId;
        $academicId = $this->academic ?? AcademicYear::where('status', "true")->first()?->id;
        $studentComment = StudentComment::query()
            ->where('student_id', $this->student->id)
            ->where('term_id', $termId)
            ->where('academic_id', $this->academic)
            ->first();
        $this->comment = $studentComment?->comment ?? '';
        $this->form->fill([
            'comment' => $this->comment,
        ]);
    }

    /**
     * Define the form schema for comments.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                MarkdownEditor::make('comment'),
            ])
            ->statePath('data');
    }

    /**
     * Save the teacher's comment for the student.
     */
    public function saveComment(): void
    {
        if (! $this->student) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Student not found.')
                ->send();
            return;
        }
        $validatedData = $this->form->getState();
        StudentComment::updateOrCreate(
            [
                'student_id' => $this->student->id,
                'term_id' => $this->termId,
                'academic_id' => $this->academic,
            ],
            [
                'comment' => $validatedData['comment'],
            ]
        );
        Notification::make()
            ->title('Comment saved successfully!')
            ->success()
            ->send();
    }

    /**
     * Define the Filament table for displaying results.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return CourseForm::with(['scoreBoard', 'subject.subjectDepot'])
                    ->where('student_id', $this->record)
                    ->when($this->termId, fn($query) => $query->where('term_id', $this->termId))
                    ->when($this->academic, fn($query) => $query->where('academic_year_id', $this->academic));
            })
            ->columns([
                TextColumn::make('subject.subjectDepot.name')->label('Course Name'),
                ...$this->getDynamicScoreBoardColumns()
            ])
            ->filters([
                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options($this->terms->pluck('name', 'id')->toArray())
                    ->default($this->termId)
                    ->searchable(),
                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options($this->academicYears->pluck('title', 'id')->toArray())
                    ->default($this->academic)
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([])
            ->filtersFormWidth('md')
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filters')
            );
    }

    /**
     * Update table data and recalculate totals/comments.
     */
    protected function updateTableData(): void
    {
        $this->getDynamicScoreBoardColumns();
        $this->calculateTotals();
        $this->loadComment();
        $this->dispatch('refreshTable');
    }

    /**
     * Calculate total, subject count, and average for the student.
     */
    public function calculateTotals(): void
    {
        if (! $this->student) {
            $this->total = $this->average = null;
            $this->totalSubject = 0;
            return;
        }
        $courseForms = CourseForm::with('scoreBoard')
            ->where('student_id', $this->record)
            ->where('term_id', $this->termId)
            ->where('academic_year_id', $this->academic)
            ->get();
        $this->total = $courseForms->reduce(function ($carry, $courseForm) {
            return $carry + $courseForm->scoreBoard
                ->where('resultSectionType.calc_pattern', 'total')
                ->sum('score');
        }, 0.0);
        $this->totalSubject = $courseForms->count();
        $this->average = $this->totalSubject > 0
            ? round($this->total / $this->totalSubject, 2)
            : 0.0;
    }

    /**
     * Get the remarks statement for a given total.
     */
    protected function remarksStatement(float $total): string
    {
        if ($total >= 80) return 'EXCELLENT';
        if ($total >= 70) return 'VERY GOOD';
        if ($total >= 65) return 'GOOD';
        if ($total >= 61) return 'CREDIT';
        if ($total >= 55) return 'CREDIT';
        if ($total >= 50) return 'CREDIT';
        if ($total >= 45) return 'PASS';
        if ($total >= 39) return 'FAIL';
        return 'FAIL';
    }

    /**
     * Dynamically generate columns for the result table based on section types.
     */
    protected function getDynamicScoreBoardColumns(): array
    {
        if (! $this->termId || ! $this->classId) {
            return [];
        }
        $dynamicFields = ResultSectionType::where('term_id', $this->termId)
            ->whereHas('resultSection', function ($query) {
                $query->where('group_id', $this->classId);
            })
            ->get(['id', 'name']);
        return $dynamicFields->map(function ($field) {
            return TextColumn::make("scoreBoard.{$field->id}")
                ->label($field->name)
                ->state(function ($record) use ($field) {
                    $score = $record->scoreBoard
                        ->where('result_section_type_id', $field->id)
                        ->pluck('score')
                        ->first();
                    return $score ?? 'N/A';
                });
        })->toArray();
    }

    /**
     * Render the Livewire component view.
     */
    public function render()
    {
        return view('livewire.teacher.student-result-details-page', [
            'errorMessage' => $this->errorMessage,
        ]);
    }
}
