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
use ZipArchive;

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
        try{
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
            $students = $this->students;
            $headings = ResultSectionType::with('resultSection')
                ->whereHas('resultSection', function ($query) use ($schoolClass) {
                    $query->where('group_id', $schoolClass->group->id);
                })
                ->get();
            $markObtained = $headings->where('calc_pattern', 'total')->first();
            $className = $schoolClass->name ?? 'N/A';
            $classTeacherName =$schoolClass->teacher->name ?? 'N/A';
            $pdfPaths = [];
            // Chunk students for memory efficiency
            collect($students)->chunk(1000)->each(function($studentChunk) use ($courses, $school, $term, $academy, $className, $classTeacherName, $headings, $markObtained, &$pdfPaths) {
                foreach ($studentChunk as $student) {
                    $studentCourses = $courses->where('student_id', $student->id);
                    $studentScores = $studentCourses->map(function ($coursex) use ($markObtained) {
                        $score = $coursex->scoreBoard->filter(function ($item) use ($markObtained) {
                            return $item['result_section_type_id'] == $markObtained->id;
                        })->first();
                        $scoreValue = $score->score ?? 0;
                        $courseName = $coursex->subject->subjectDepot->name;
                        $passMark = $coursex->subject->pass_mark;
                        $isPassed = $scoreValue >= $passMark;
                        return [
                            'subject' => $coursex->subject->subjectDepot->name,
                            'score' => $score->score ?? 'N/A',
                            'pass_mark' => $passMark,
                            'status' => $isPassed ? 'Pass' : 'Fail',
                        ];
                    });
                    $data = [
                        'school'=>$school,
                        'className' => $className,
                        'classTeacherName' => $classTeacherName,
                        'term'=>$term,
                        'academy'=>$academy,
                        'courses'=>$studentCourses,
                        'student' => $student,
                        'scores' => $studentScores,
                        'headings'=> $headings,
                        'markObtained'=>$markObtained
                    ];
                    $pdf = Pdf::loadView('template.student_result',compact('data'))->setPaper('A4', 'portrait');
                    $time = time();
                    $fileName = "result-{$student->id}-$time.pdf";
                    $filePath = "results/{$fileName}";
                    Storage::disk('s3')->put($filePath, $pdf->output());
                    $pdfPaths[] = $filePath;
                }
            });
            // Zip all PDFs
            $zipFileName = 'results/bulk_results_' . time() . '.zip';
            $zip = new ZipArchive;
            $localZipPath = storage_path('app/' . basename($zipFileName));
            if ($zip->open($localZipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($pdfPaths as $pdfPath) {
                    $localPdf = tempnam(sys_get_temp_dir(), 'pdf');
                    file_put_contents($localPdf, Storage::disk('s3')->get($pdfPath));
                    $zip->addFile($localPdf, basename($pdfPath));
                }
                $zip->close();
                Storage::disk('s3')->put($zipFileName, file_get_contents($localZipPath));
                unlink($localZipPath);
            }
            $downloadStatus = DownloadStatus::whereId($this->downId)->first();
            $downloadStatus->status ='completed';
            $downloadStatus->download_links = Storage::disk('s3')->url($zipFileName);
            $downloadStatus->save();
            Notification::make()
                ->title('Your bulk download is ready')
                ->success()
                ->send();
        }catch (\Exception $e) {
            $downloadStatus = DownloadStatus::whereId($this->downId)->first();
            $downloadStatus->status ='failed';
            $downloadStatus->download_links = "";
            $downloadStatus->error = $e->getMessage();
            $downloadStatus->save();
            Notification::make()
            ->title('Your download failed')
            ->danger()
            ->send();
        }
    }
}
