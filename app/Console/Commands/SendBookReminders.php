<?php

namespace App\Console\Commands;

use App\Models\Library;
use App\Models\LibraryBookLoan;
use Illuminate\Console\Command;

class SendBookReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:send-reminders';
    protected $description = 'Send reminders for overdue and due soon books';

    /**
     * The console command description.
     *
     * @var string
     */


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Books due in 2 days
        $dueSoon = LibraryBookLoan::where('status', 'borrowed')
            ->whereDate('due_date', now()->addDays(2))
            ->with(['book', 'borrower'])
            ->get();

        foreach ($dueSoon as $loan) {
            $loan->borrower->notify(new \App\Notifications\BookDueSoon($loan));
        }

        // Overdue books
        $overdue = LibraryBookLoan::where('status', 'borrowed')
            ->whereDate('due_date', '<', now())
            ->with(['book', 'borrower'])
            ->get();

        foreach ($overdue as $loan) {
            $loan->borrower->notify(new \App\Notifications\BookOverdue($loan));
            $loan->update(['status' => 'overdue']);
        }

        $this->info('Reminders sent successfully.');
    }
}
