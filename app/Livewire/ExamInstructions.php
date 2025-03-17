<?php

namespace App\Livewire;

use Livewire\Component;

class ExamInstructions extends Component
{
    public $showModal = false;

    // Define your instructions as an array
    public $instructions = [];

    public $records;

    // Introduction text
    public $introduction = "Welcome to the online exam! Please read the instructions carefully before you begin. Make sure you are ready to take the exam in a quiet environment.";

    public function mount($records)
    {

        $this->records = $records;

        // Initialize the instructions array
        $this->instructions = [
            "Ensure you have a stable internet connection before starting the exam.",
            "Use a compatible browser for the best experience.",
            "Make sure your camera and microphone are working properly.",
            "Find a quiet space to take the exam without interruptions.",
            "Read each question carefully before selecting your answer.",
            "Do not refresh the page during the exam as it may cause data loss.",
            "Note the time limit for the exam and manage your time effectively.",
            "Use the 'Next' button to proceed to the next question.",
            "Review your answers before submitting the exam.",
            "Contact support if you encounter any technical issues.",
            "Maintain academic integrity throughout the exam.",
            "Do not share your answers with other participants.",
            "Follow any additional instructions provided by your instructor.",
            "Take breaks if needed, but be aware of the time running out.",
            "Ensure you have completed all sections of the exam.",
            "Log out of the exam portal once you are done.",
            "Review the exam rules and regulations before starting.",
            "Ensure your device is fully charged or plugged in during the exam.",
            "Avoid using unauthorized materials during the exam.",
            "Have a positive mindset and do your best!"
        ];
    }

    public function startExam()
    {
        $this->showModal = true; // Show confirmation modal
    }

    public function confirmStart()
    {
        // return redirect()->route('exam.page', ['records'=> $this->records]); // Change this to your exam page URL
        return redirect()->route('student.exam.take', ['exam'=>$this->records]);
    }

    public function cancelStart()
    {
        $this->showModal = false; // Hide modal
    }

    public function render()
    {
        return view('livewire.exam-instructions');
    }
}
