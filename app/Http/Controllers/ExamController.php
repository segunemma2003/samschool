<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\Exam;
use App\Models\QuizScore;
use App\Models\QuizSubmission;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function takeExam($examId)
    {

        $exam = Exam::with([
            'subject',
            'subject.subjectDepot', // Load the subjectDepot through subject
            'questions' // Load the related questions
        ])->findOrFail($examId);
        $user = Auth::user();
        $student = Student::whereEmail($user->email)->firstOrFail();
        $course = CourseForm::where('subject_id', $exam->subject_id)
        ->where('student_id', $student->id)
        ->where('academic_year_id', $exam->academic_year_id)
        ->firstOrFail();

        $term = Term::where('id', $exam->term_id)->first();
        $academy = AcademicYear::where('id', $exam->academic_year_id)->first();

        $questions = $exam->questions->toArray();
        $quizScore = QuizScore::where('exam_id', $exam->id)
        ->where('student_id', $student->id)
        ->first();
        $answers = QuizSubmission::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->get()
                    ->toArray();
        shuffle($questions);
        // dd($exam);
        return view('exam.react.take_exam',compact('student', 'exam','course', 'questions', 'answers', 'quizScore', 'term', 'academy'));
    }
}
