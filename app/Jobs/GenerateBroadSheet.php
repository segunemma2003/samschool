<?php

namespace App\Jobs;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DownloadStatus;
use App\Models\ResultSectionType;
use App\Models\SchoolClass;
use App\Models\SchoolInformation;
use App\Models\Term;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateBroadSheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $data;
    protected $students;
    protected $downId;
    /**
     * Create a new job instance.
     */
    public function __construct($data, $students, $downId)
    {
        $this->data = $data;
        $this->students = $students;
        $this->downId = $downId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Log::info('Handle method started in GenerateStudentResultPdf', ['data' => $this->data, 'students' => $this->students]);
        try{
            // Log::info('Starting PDF generation for student: ' );
            $academy = AcademicYear::find($this->data['academic_id']);
            $term = Term::find($this->data['term_id']);
            $school = SchoolInformation::where([
                ['term_id', $term->id],
                ['academic_id', $academy->id]
            ])->first();


            $courses = CourseForm::with([
                'subject.subjectDepot',
                'scoreBoard'
            ])->where([
                    ['academic_year_id', $academy->id],
                    ['term_id', $term->id]
                ])
                ->get();
            $schoolClass = SchoolClass::whereId($this->data['class_id'])->first();
                // Log::info($schoolClass->group->id);
                // dd($schoolClass);
                $students = $this->students;
                $headings = ResultSectionType::with('resultSection')
                ->whereHas('resultSection', function ($query) use ($schoolClass) {
                    $query->where('group_id', $schoolClass->group->id);
                })
                ->get();
                $markObtained = $headings->where('calc_pattern', 'total')->first();
                $className = $schoolClass->name ?? 'N/A';
                $classTeacherName =$schoolClass->teacher->name ?? 'N/A';
                $totalSubjects = 0;
                $studentData = [];
                $courseTotals = [];
                $totalPassed = 0;
                foreach ($this->students as $student) {
                    $studentCourses = $courses->where('student_id', $student->id);
                    $passCount = 0;
                    $passedEnglish = false;
                    $passedMaths = false;
                    // Log::info('Student Courses:', ['student_id' => $student->id, 'courses' => $studentCourses]);
                    $studentScores = $studentCourses->map(function ($coursex) use ($markObtained, &$passCount, &$passedEnglish, &$passedMaths) {
                        Log::info('Processing Course:', [
                            // 'course' => $coursex,
                            // 'markObtained' => $markObtained,
                            'scoreBoard' => $coursex->scoreBoard,
                            'markOb'=>$markObtained->id
                            // 'scoreBoard2' => $coursex->scoreBoard,
                        ]);
                        $score = $coursex->scoreBoard->filter(function ($item) use ($markObtained) {
                            Log::info('Checking Item:', ['item_id' => $item['result_section_type_id'], 'markObtained_id' => $markObtained->id]);
                            return $item['result_section_type_id'] == $markObtained->id;
                        })->first();
                        Log::info('Score Found:', ['score' => $score]);
                        $scoreValue = $score->score ?? 0;
                        $courseName = $coursex->subject->subjectDepot->name;
                        $passMark = $coursex->subject->pass_mark;
                        // Check if the student passed this subject
                        $isPassed = $scoreValue >= $passMark;

                        if ($isPassed) {
                            $passCount++;
                            if (strtolower($courseName) === 'english') {
                                $passedEnglish = true;
                            } elseif (in_array(strtolower($courseName), ['math', 'mathematics'])) {
                                $passedMaths = true;
                            }
                        }
                        if (!isset($courseTotals[$courseName])) {
                            $courseTotals[$courseName] = 0;
                        }
                        $courseTotals[$courseName] += $scoreValue;

                        return [
                            'subject' => $coursex->subject->subjectDepot->name,
                            'score' => $score->score ?? 'N/A',
                            'pass_mark' => $passMark,
                            'status' => $isPassed ? 'Pass' : 'Fail',
                        ];
                    });
 // Determine the final remark
                    $remark = ($passedEnglish && $passedMaths && $passCount >= 6) ? 'Pass' : 'Fail';
                    $studentData[] = [
                        'student' => $student,
                        'scores' => $studentScores,
                        'remark' => $remark,
                    ];
                    if($remark == "Pass"){
                        $totalPassed += 1;
                    }
                }

                $passPercent = ($totalPassed/count($this->students))*100;
                Log::info(  $studentData);
                // dd($markObtained);
            $data = [
                'school'=>$school,
                'totalPassed'=>$totalPassed,
                'passPercent'=>$passPercent,
                'className' => $className,
                'classTeacherName' => $classTeacherName,
                'term'=>$term,
                'academy'=>$academy,
                'courses'=>$courses,
                // 'students'=> $this->students,
                'students' => $studentData,
                'headings'=> $headings,
                'markObtained'=>$markObtained
            ];
            $students= $this->students;
            if (empty($data['students'])) {
                throw new \Exception('No student data found. Please check the input.');
            }
            // Log::info('PDF Data:', compact('data'));
            // dd($students);
            $subjectCount = count($data['students'][0]['scores']);
            $baseWidth = 842; // Base width for A4 landscape in points
            $extraWidthPerSubject = 40; // Approximate additional width per subject
            $calculatedWidth = $baseWidth + ($extraWidthPerSubject * $subjectCount);
            $pdf = Pdf::loadView('template.broadsheet',compact('data'))->setPaper([0, 0, $calculatedWidth, 595], 'landscape');
            $time = time();
            $fileName = "broadsheet-$time.pdf";
            $filePath = "results/{$fileName}";
            Storage::disk('cloudinary')->put($filePath, $pdf->output());
            $downloadStatus = DownloadStatus::whereId($this->downId)->first();
            $downloadStatus->status ='completed';
            $downloadStatus->download_links = Storage::disk('cloudinary')->url($filePath);
            $downloadStatus->save();
            Notification::make()
                ->title('Your download is ready')
                ->success()
                ->send();

        }catch (QueryException $e) {
            $downloadStatus = DownloadStatus::whereId($this->downId)->first();
            $downloadStatus->status ='failed';
            $downloadStatus->download_links = "";
            $downloadStatus->error = $e->getMessage();
            $downloadStatus->save();

            Notification::make()
            ->title('Your download failed')
            ->danger()
            ->send();
            // Handle database errors
            Log::error('Database error:', ['error' => $e->getMessage()]);
        }
        catch (Throwable $e) {
            $downloadStatus = DownloadStatus::whereId($this->downId)->first();
            $downloadStatus->status ='failed';
            $downloadStatus->download_links = "";
            $downloadStatus->error = $e->getMessage();
            $downloadStatus->save();

            Notification::make()
            ->title('Your download failed')
            ->danger()
            ->send();
            // Handle all other errors
            Log::error('Error:', ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            $downloadStatus = DownloadStatus::whereId($this->downId)->first();
            $downloadStatus->status ='failed';
            $downloadStatus->download_links = "";
            $downloadStatus->error = $e->getMessage();
            $downloadStatus->save();

            Notification::make()
            ->title('Your download failed')
            ->danger()
            ->send();
            // Log the error for debugging purposes
            Log::error('Error generating student result PDF:', ['error' => $e->getMessage()]);
        }

    }
}
