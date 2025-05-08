<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\AcademicYear;
use App\Models\Arm;
use App\Models\ArmsTeacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StudentOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Number of Subjects', $this->getTotalNumberOfSubjects())
                ->icon('heroicon-o-book-open')
                ->color('success'),
            Stat::make('Total Number of Students in Arms Class', $this->getTotalNumberOfStudents())
                ->icon('heroicon-o-users')
                ->color('primary'),
        ];
    }


    public function getTotalNumberOfSubjects()
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        $subjects = Subject::where('teacher_id', $teacher->id)->count();
        return $subjects;
    }

    public function getTotalNumberOfStudents()
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        $term = Term::where('status', 'true')->first();
        $academicYear = AcademicYear::where('status', 'true')->first();
        $armTeacher = ArmsTeacher::where(['academic_id'=>$academicYear->id,'term_id'=>$term->id,'teacher_id'=> $teacher->id])->first();
        if(is_null($armTeacher)){
            return 0;
        }else{
            $arm_id = $armTeacher->arm_id;
            $class_id = $armTeacher->class_id;
            $students = Student::where('arm_id', $arm_id)
                ->where('class_id', $class_id)
                ->count();
            return $students;
        }

    }
}


