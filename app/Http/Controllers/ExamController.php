<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\Exam;
use App\Models\ExamRecording;
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
        $student = Student::with('class')->whereEmail($user->email)->firstOrFail();
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


public function saveExamData(Request $request)
    {
        $validatedData = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'course_form_id' => 'required|exists:course_forms,id',
            'recording_path' => 'nullable|string', // Optional field
            'total_score' => 'required|numeric',
            'answers' => 'required|array', // Array of answers
            'answers.*.question_id' => 'required|exists:question_banks,id',
            'answers.*.answer' => 'nullable|string',
            'answers.*.score' => 'required|numeric',
            'answers.*.correct' => 'required|boolean',
            'answers.*.comments' => 'nullable|string'
        ]);

        try {
            // Save exam recording if provided
            if (!empty($validatedData['recording_path'])) {
                ExamRecording::create([
                    'exam_id' => $validatedData['exam_id'],
                    'student_id' => $validatedData['student_id'],
                    'recording_path' => $validatedData['recording_path'],
                    'recorded_at' => now(),
                ]);
            }

            // Save or update QuizScore
            $quizScore = QuizScore::updateOrCreate(
                [
                    'course_form_id' => $validatedData['course_form_id'],
                    'student_id' => $validatedData['student_id'],
                    'exam_id' => $validatedData['exam_id']
                ],
                ['total_score' => $validatedData['total_score'], 'comments' => "submitted"]
            );

            // Save or update Quiz Submissions (Loop through answers)
            foreach ($validatedData['answers'] as $answerData) {
                QuizSubmission::updateOrCreate(
                    [
                        'course_form_id' => $validatedData['course_form_id'],
                        'student_id' => $validatedData['student_id'],
                        'exam_id' => $validatedData['exam_id'],
                        'question_id' => $answerData['question_id']
                    ],
                    [
                        'quiz_score_id' => $quizScore->id,
                        'answer' => $answerData['answer'] ?? null,
                        'score' => $answerData['score'],
                        'correct' => $answerData['correct'],
                        'comments' => $answerData['comments'] ?? "submitted",
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Exam data saved successfully!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save exam data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
