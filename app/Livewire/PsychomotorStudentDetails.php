<?php

namespace App\Livewire;

use App\Models\PyschomotorStudent;
use App\Models\Psychomotor;
use App\Models\Term;
use Filament\Notifications\Notification;
use Livewire\Component;

class PsychomotorStudentDetails extends Component
{
    public $record; // ID of the student
    public $psychomotors;
    public $ratings = [];
    public $hasInlineLabel = true;
    public $term_id;
    public $term;


    public function mount($record)
    {
        $this->record = $record;

        // Load all psychomotors and related student ratings

$term = Term::where('status',"true")->first();
$this->term = $term;
// dd($term);
$this->psychomotors = Psychomotor::where('term_id', $term->id)->get();
$this->term_id = $term->id;
        foreach ($this->psychomotors as $psychomotor) {
            $existingRating = PyschomotorStudent::where('student_id', $record)
                ->where('psychomotor_id', $psychomotor->id)

                ->first();

            $this->ratings[$psychomotor->id] = [
                'rating' => $existingRating->rating ?? 1,
                'comment' => $existingRating->comment ?? '',
            ];
        }
    }

    public function save()
    {
        foreach ($this->ratings as $psychomotorId => $data) {
            PyschomotorStudent::updateOrCreate(
                [
                    'student_id' => $this->record,
                    'psychomotor_id' => $psychomotorId,
                ],
                [
                    'rating' => $data['rating'],
                    'comment' => $data['comment'] ?? " ",
                ]
            );
        }

        // Send Filament notification
        Notification::make()
            ->success()
            ->title('Saved Successfully')
            ->body('Psychomotor details have been saved for the student.')
            ->send();

        $this->mount($this->record); // Refresh data
    }

    public function render()
    {
        return view('livewire.psychomotor-student-details');
    }
}
