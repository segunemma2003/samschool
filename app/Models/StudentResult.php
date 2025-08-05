<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'academic_year_id',
        'class_id',
        'total_score',
        'average_score',
        'grade',
        'remarks',
        'total_subjects',
        'teacher_comment',
        'commented_by',
        'calculated_data',
        'calculated_at',
        'calculation_status',
    ];

    protected $casts = [
        'calculated_data' => 'array',
        'calculated_at' => 'datetime',
        'total_score' => 'decimal:2',
        'average_score' => 'decimal:2',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function commentedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commented_by');
    }

    // Scopes
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('calculation_status', 'completed');
    }

    // Helper methods
    public function getSubjectResults()
    {
        return $this->calculated_data['subjects'] ?? [];
    }

    public function getSummary()
    {
        return $this->calculated_data['summary'] ?? [];
    }

    public function getMetadata()
    {
        return $this->calculated_data['metadata'] ?? [];
    }

    public function isComplete(): bool
    {
        return $this->calculation_status === 'completed';
    }
}
