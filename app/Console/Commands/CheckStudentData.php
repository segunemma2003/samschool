<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Group;

class CheckStudentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student:check {studentId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check student data and identify missing class/group assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentId = $this->argument('studentId');

        $this->info("Checking student data for ID: {$studentId}");
        $this->line('');

        // Get student with relationships
        $student = Student::with(['class', 'class.group'])->find($studentId);

        if (!$student) {
            $this->error("Student with ID {$studentId} not found!");
            return 1;
        }

        $this->info("Student: {$student->name} (ID: {$student->id})");
        $this->line('');

        // Check class assignment
        if (!$student->class_id) {
            $this->error("❌ Student is not assigned to any class!");
            $this->line('');

            // Show available classes
            $classes = ClassRoom::all();
            if ($classes->count() > 0) {
                $this->info("Available classes:");
                foreach ($classes as $class) {
                    $this->line("  - ID: {$class->id}, Name: {$class->name}");
                }
                $this->line('');
                $this->comment("To fix: Update student's class_id to one of the above IDs");
            } else {
                $this->error("No classes found in the system!");
                $this->comment("To fix: Create classes first");
            }
            return 1;
        }

        $this->info("✅ Student is assigned to class: {$student->class->name} (ID: {$student->class->id})");

        // Check group assignment
        if (!$student->class->group_id) {
            $this->error("❌ Student's class is not assigned to any group!");
            $this->line('');

            // Show available groups
            $groups = Group::all();
            if ($groups->count() > 0) {
                $this->info("Available groups:");
                foreach ($groups as $group) {
                    $this->line("  - ID: {$group->id}, Name: {$group->name}");
                }
                $this->line('');
                $this->comment("To fix: Update class's group_id to one of the above IDs");
            } else {
                $this->error("No groups found in the system!");
                $this->comment("To fix: Create groups first");
            }
            return 1;
        }

        $this->info("✅ Student's class is assigned to group: {$student->class->group->name} (ID: {$student->class->group->id})");
        $this->line('');
        $this->info("✅ Student data is properly configured!");

        return 0;
    }
}
