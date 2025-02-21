<?php
namespace App\Traits;

use App\Models\ExamRecording;
use Illuminate\Support\Facades\Storage;

trait HandlesVideoRecording
{
    public bool $isRecording = false;
    private array $recordingChunks = [];

    protected function startRecording(): void
    {
        $this->isRecording = true;
        $this->dispatch('startRecording');
    }

    protected function stopRecording(): void
    {
        if ($this->isRecording) {
            $this->isRecording = false;
            $this->dispatch('stopRecording');
        }
    }

    public function handleRecordingChunk($chunk): void
    {
        $this->recordingChunks[] = $chunk;

        if (count($this->recordingChunks) >= 10) { // Upload every 10 chunks
            $this->uploadRecordingChunks();
        }
    }

    private function uploadRecordingChunks(): void
    {
        if (empty($this->recordingChunks)) return;

        $combinedBlob = $this->combineChunks($this->recordingChunks);
        $path = "recordings/{$this->examId}/{$this->studentId}/" . uniqid() . '.webm';

        Storage::disk('s3')->put($path, $combinedBlob);

        ExamRecording::create([
            'exam_id' => $this->examId,
            'student_id' => $this->studentId,
            'recording_path' => $path,
            'recorded_at' => now(),
            'chunk_number' => count($this->recordingChunks),
        ]);

        $this->recordingChunks = [];
    }

    private function combineChunks(array $chunks): string
    {
        return implode('', array_map('base64_decode', $chunks));
    }
}
