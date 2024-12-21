<?php

namespace App\Jobs;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\DownloadStatus;
use App\Models\PsychomotorCategory;
use App\Models\ResultSectionType;
use App\Models\SchoolClass;
use App\Models\SchoolInformation;
use App\Models\StudentAttendanceSummary;
use App\Models\StudentComment;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GenerateStudentResultPdf implements ShouldQueue
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
        Log::info('Handle method started in GenerateStudentResultPdf', ['data' => $this->data, 'students' => $this->students]);
        $fileUrl = "";
        $fileUrls = [];
        try {
        foreach ($this->students as $student) {
            Log::info('Starting PDF generation for student: ' . $student->id);
            $academy = AcademicYear::find($this->data['academic_id']);
            $term = Term::find($this->data['term_id']);

            $school = SchoolInformation::where([
                ['term_id', $term->id],
                ['academic_id', $academy->id]
            ])->first();

            $studentAttendance = StudentAttendanceSummary::where([
                ['term_id', $term->id],
                ['student_id', $student->id],
                ['academic_id', $academy->id]
            ])->first();

            $studentComment = StudentComment::where([
                ['student_id', $student->id],
                ['term_id', $term->id],
                ['academic_id', $academy->id]
            ])->first();

            // Eager load related data for courses
            $courses = CourseForm::with([
                'subject.subjectDepot',
                'scoreBoard'
            ])->where([
                    ['student_id', $student->id],
                    ['academic_year_id', $academy->id],
                    ['term_id', $term->id]
                ])
                ->get();

        // Optimize headings query with eager loading
        $headings = ResultSectionType::with('resultSection')
        ->whereHas('resultSection', function ($query) use ($student) {
            $query->where('group_id', $student->class->group->id);
        })
        ->get();

        $psychomotorCategory = PsychomotorCategory::all();

        // Group headings by patterns
        $markObtained = $headings->whereIn('calc_pattern', ['input', 'total']) ?? collect([]);
        $studentSummary = $headings->whereIn('calc_pattern', ['position', 'grade_level']) ?? collect([]);
        $termSummary = $headings->whereIn('calc_pattern', ['class_average', 'class_highest_score', 'class_lowest_score']) ?? collect([]);
        $remarks = $headings->whereIn('calc_pattern', ['remarks']) ?? collect([]);

        $class = SchoolClass::where('id', $student->class->id)->first() ?? collect([]);
        $totalSubject =count($courses);

        $totalHeadings = $headings->where('calc_pattern', 'total');

        // Step 2: Initialize a variable to store the total sum
        $totalScore = 0;
        $englishScore = 0;
        $mathScore = 0;

        $totalScore = $courses->reduce(function ($carry, $course) use ($totalHeadings, &$englishScore, &$mathScore) {
            foreach ($totalHeadings as $heading) {
                $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                $scoreValue = $score->score ?? 0;

                // Add to the total score
                $carry += $scoreValue;

                // Check if the subject is English or Maths and store their scores
                $subject = $course->subject->subjectDepot->name;
                if ((strncasecmp($subject, 'english', 7) === 0) || (strncasecmp($subject, 'literacy', 8) === 0)){
                    $englishScore = $scoreValue;
                } elseif ((strncasecmp($subject,'math', 4)  == 0)|| (strncasecmp($subject, 'numeracy', 8) === 0)) {
                    $mathScore = $scoreValue;
                }
            }
            return $carry;
        }, 0);


        $percent = round(($totalScore / $totalSubject));
        // dd($percent);

        $principalComment = $this->getPerformanceComment($percent, $englishScore, $mathScore);

        $data = [
            'class'=>$class,
            'totalSubject'=>$totalSubject,
            'totalScore'=>$totalScore,
            'percent'=>$percent,
            'markObtained'=>$markObtained,
            'remarks'=>$remarks,
            'studentSummary'=> $studentSummary,
            'termSummary'=>$termSummary,
            'courses'=>$courses,
            'studentComment'=>$studentComment,
            'student'=>$student,
            'school'=>$school,
            'academy'=>$academy,
            'studentAttendance'=>$studentAttendance,
            'term'=>$term,
            'principalComment' => $principalComment,
            'psychomotorCategory'=> $psychomotorCategory
        ];


        $time = time();
        // $html = view('results.template', $data)->render();

        // $pdf = SnappyPdf::loadView('results.template', $data);
        $pdf = Pdf::loadView('results.template',compact('psychomotorCategory','class','markObtained','remarks','studentSummary','termSummary','courses','studentComment','student', 'school', 'academy', 'studentAttendance', 'term', 'totalScore','totalSubject', 'percent','principalComment'))->setPaper('a4', 'portrait');
        $fileName = "result-{$student->id}-{$student->name}.pdf";
        $filePath = "results/{$fileName}";
        Storage::disk('cloudinary')->put($filePath, $pdf->output());
        Log::info("Uploading file to: {$filePath}");
        Log::info("Generated URL: " . Storage::disk('cloudinary')->url($filePath));
        $fileUrls[] =  Storage::disk('cloudinary')->url($filePath);


          // $pdf = Pdf::loadView('results.template',compact('psychomotorCategory','class','markObtained','remarks','studentSummary','termSummary','courses','studentComment','student', 'school', 'academy', 'studentAttendance', 'term', 'totalScore','totalSubject', 'percent','principalComment'))->setPaper('a4', 'portrait');
                    // return response()->streamDownload(
                    //     fn () => print($pdf->output()),
                    //     "result-{$record->name}.pdf"
                    // );

        }

        $zip = new ZipArchive();
        $mtime = time();
        $zipFileName = "results_{$mtime}.zip";
        Log::info($zipFileName);
        $zipTempPath = tempnam(sys_get_temp_dir(), 'zip');
        Log::info($zipTempPath);
        $opened = $zip->open($zipTempPath, ZipArchive::CREATE);
        if ($opened === TRUE) {
            Log::info('ZIP file opened successfully');
        } else {
            Log::error('Failed to open ZIP file');
        }
        foreach ($fileUrls as $fileUrl) {
            try {
                $fileName = basename($fileUrl);  // Get the file name from the URL
                $fileContent = file_get_contents($fileUrl); // Get the file contents
                if ($fileContent === false) {
                    throw new \Exception("Unable to fetch file content.");
                }

                // Create a unique name for the temporary file
                $tim = time();
                $tempFilePath = public_path("temp/result-{$tim}.pdf");

                // Make sure the temp directory exists
                if (!file_exists(public_path('temp'))) {
                    mkdir(public_path('temp'), 0777, true);
                }

                // Save the file content to a temporary file
                file_put_contents($tempFilePath, $fileContent);

                // Add the temporary file to the ZIP archive
                $zip->addFile($tempFilePath, "result-{$tim}.pdf");

                // Optional: Clean up temporary files after adding them to the ZIP
                // unlink($tempFilePath);

            } catch (\Exception $e) {
                Log::error('Error fetching file for ZIP:', ['url' => $fileUrl, 'error' => $e->getMessage()]);
            }
        }


        $zip->close();

        if (file_exists($zipTempPath)) {
            $zipFileContent = file_get_contents($zipTempPath);
            // Log::info($zipFileContent);
            $zipFilePathCloud = "results/{$zipFileName}";
            Log::info('Cloudinary file upload attempted.', ['path' => $zipFilePathCloud, 'content' => substr($zipFileContent, 0, 10)]);

            try {
                $stored = Storage::disk('cloudinary')->put($zipFilePathCloud, $zipFileContent);
                if ($stored) {
                    Log::info("File successfully uploaded to Cloudinary: {$zipFilePathCloud}");
                } else {
                    Log::error("File upload to Cloudinary failed for: {$zipFilePathCloud}");
                }
            } catch (\Exception $e) {
                Log::error('Error during Cloudinary upload:', ['message' => $e->getMessage()]);
            }
            Log::info($stored);
            $zipUrl = Storage::disk('cloudinary')->url($zipFilePathCloud);
            Log::info('ZIP file URL:', ['url' => $zipUrl]);
            $downloadStatus = DownloadStatus::whereId($this->downId)->first();
            $downloadStatus->status ='completed';
            $downloadStatus->download_links = $zipUrl;
            $downloadStatus->save();

            foreach ($fileUrls as $fileUrl) {
                $fileName = basename($fileUrl);
                Storage::cloud()->delete("results/{$fileName}");
            }

        // Optionally delete the temp ZIP file from local storage
            // unlink($zipTempPath);
        }
        Notification::make()
            ->title('Your download is ready')
            ->success()
            ->send();

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


    public function getPerformanceComment($percentage, $englishScore, $mathScore) {
        // Define comments based on percentage range
        $comments = [
            'excellent' => [
                "Excellent Performance",
                "Keep it up",
                "Do not relent on your efforts. Keep it up"
            ],
            'very_good' => [
                "A Very Good Performance",
                "You are doing well. Keep pushing",
                "An impressive effort!"
            ],
            'hardworking' => [
                "A Hardworking Learner",
                "Your effort is commendable",
                "Good work, but you can achieve more"
            ],
            'work_harder' => [
                "Work Harder For a better result",
                "Keep improving",
                "Better focus next term will yield results"
            ],
            'put_in_effort' => [
                "Put In More Effort",
                "Your performance can improve",
                "Stay committed to better outcomes"
            ],
            'be_serious' => [
                "Be more serious in your studies",
                "Focus more on your academic goals",
                "A stronger commitment is needed"
            ],
            'english_math_fail' => [
                "Tried but should try harder next term for a better result in English and Mathematics",
                "Put in more effort in English and Mathematics",
                "A good result but can do better in English and Mathematics"
            ],
          'math_fail' => [
                "Tried but should try harder next term for a better result in Mathematics",
                "Put in more effort in Mathematics",
                "A good result but can do better in Mathematics"
            ],
          'english_fail' => [
                "Tried but should try harder next term for a better result in English",
                "Put in more effort in English",
                "A good result but can do better in English"
            ],
            'good_result' => [
                "Good results but can be better if you put in more effort",
                "Excellent Performance. Keep it up",
                "A good result. Keep it up"
            ],
            'improve_next_term' => [
                "Work harder next term for better results",
                "There is still room for improvement next term",
                "Stay focused for better performance next term"
            ]
        ];
    if ($percentage >= 70) {
        // Check English and Mathematics scores first
        if ($englishScore < 50 && $mathScore < 50) {
                return $comments['english_math_fail'][array_rand($comments['english_math_fail'])];
        } elseif ($englishScore < 50) {
            return $comments['english_fail'][array_rand($comments['english_fail'])];
        } elseif ($mathScore < 50) {
          return $comments['math_fail'][array_rand($comments['math_fail'])];
        }
      }

        // Determine the comment based on percentage
        if ($percentage >= 86) {
            $performanceComment = $comments['excellent'][array_rand($comments['excellent'])];
        } elseif ($percentage >= 75) {
            $performanceComment = $comments['very_good'][array_rand($comments['very_good'])];
        } elseif ($percentage >= 65) {
            $performanceComment = $comments['hardworking'][array_rand($comments['hardworking'])];
        } elseif ($percentage >= 51) {
            $performanceComment = $comments['work_harder'][array_rand($comments['work_harder'])];
        } elseif ($percentage >= 41) {
            $performanceComment = $comments['put_in_effort'][array_rand($comments['put_in_effort'])];
        } else {
            $performanceComment = $comments['be_serious'][array_rand($comments['be_serious'])];
        }

        return $performanceComment;
    }


}
