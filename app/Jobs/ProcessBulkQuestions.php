<?php

namespace App\Jobs;

use App\Models\QuestionBank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkQuestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $questionsData,
        private int $examId
    ) {}

    public function handle(): void
    {
        $chunks = array_chunk($this->questionsData, 20);

        foreach ($chunks as $chunk) {
            QuestionBank::insert($chunk);
        }
    }
}
