<?php

namespace App\Http\Controllers;

use App\Models\CourseForm;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function takeExam($examId)
    {
        $exam = Exam::findOrFail($examId);
        $user = Auth::user();
        $student = Student::whereEmail($user->email)->firstOrFail();

        $course = CourseForm::where('subject_id', $exam->subject_id)
            ->where('student_id', $student->id)
            ->where('academic_year_id', $exam->academic_year_id)
            ->firstOrFail();

        // Prepare data for React
        $examData = [
            'examId' => $exam->id,
            'userName' => $student->name,
            'subject' => $exam->subject->subjectDepot->name,
            'quizTitle' => $exam->details,
            'duration' => $exam->duration,
            'questions' => $exam->questions, // Assuming relation exists
            'studentId' => $student->id,
            'courseFormId' => $course->id,
        ];

        return view('exam.react.take_exam', compact('examData'));
    }
}
