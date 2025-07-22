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
    protected ?string $heading = 'Student Overview';
    protected ?string $description = 'Quick stats about your students and subjects';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Subjects', $this->getTotalNumberOfSubjects())
                ->icon('heroicon-o-book-open')
                ->color('info')
                ->description('Subjects you teach')
                ->descriptionIcon('heroicon-m-academic-cap'),
            Stat::make('Students in Your Class', $this->getTotalNumberOfStudents())
                ->icon('heroicon-o-users')
                ->color('success')
                ->description('Current class roster')
                ->descriptionIcon('heroicon-m-user-group'),
        ];
    }


    public function getTotalNumberOfSubjects()
    {
        $user = \App\Models\User::whereId(\Illuminate\Support\Facades\Auth::id())->first();
        $teacher = \App\Models\Teacher::whereEmail($user->email)->first();
        $cacheKey = "teacher_subject_count_{$teacher->id}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($teacher) {
            return \App\Models\Subject::where('teacher_id', $teacher->id)->count();
        });
    }

    public function getTotalNumberOfStudents()
    {
        $user = \App\Models\User::whereId(\Illuminate\Support\Facades\Auth::id())->first();
        $teacher = \App\Models\Teacher::whereEmail($user->email)->first();
        $term = \App\Models\Term::where('status', 'true')->first();
        $academicYear = \App\Models\AcademicYear::where('status', 'true')->first();
        $armTeacher = \App\Models\ArmsTeacher::where(['academic_id'=>$academicYear->id,'term_id'=>$term->id,'teacher_id'=> $teacher->id])->first();
        if(is_null($armTeacher)){
            return 0;
        }else{
            $arm_id = $armTeacher->arm_id;
            $class_id = $armTeacher->class_id;
            $cacheKey = "teacher_student_count_{$teacher->id}_{$term->id}_{$academicYear->id}";
            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($arm_id, $class_id) {
                return \App\Models\Student::where('arm_id', $arm_id)
                    ->where('class_id', $class_id)
                    ->count();
            });
        }
    }
}


