<?php

namespace App\Http\Controllers;

use App\Models\ExamRecording;
use Illuminate\Http\Request;

class ExamRecordingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/webm,video/mp4|max:102400',
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id'
        ]);

        try {
            // Store the video file
            $path = $request->file('video')->store('exam-recordings', 'private');

            // Create recording record
            $recording = ExamRecording::create([
                'exam_id' => $request->exam_id,
                'student_id' => $request->student,
                'recording_path'=>$path
            ]);
        }catch(\Exception $e){

        }
}
}
