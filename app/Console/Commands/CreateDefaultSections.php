<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentGroup;

class CreateDefaultSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sections:create-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default school sections (Primary, Secondary, JSS, SSS)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating default school sections...');

        $defaultSections = [
            'Primary',
            'Secondary',
            'JSS',
            'SSS',
            'General'
        ];

        $createdCount = 0;

        foreach ($defaultSections as $sectionName) {
            // Check if section already exists
            $existingSection = StudentGroup::where('name', $sectionName)->first();

            if ($existingSection) {
                $this->line("Section '{$sectionName}' already exists - skipping");
                continue;
            }

            // Create the section
            StudentGroup::create(['name' => $sectionName]);
            $this->info("✓ Created section: {$sectionName}");
            $createdCount++;
        }

        if ($createdCount > 0) {
            $this->info("\n✅ Successfully created {$createdCount} new sections!");
            $this->line("\nNext steps:");
            $this->line("1. Go to Classes section in admin panel");
            $this->line("2. Edit each class and assign it to a section");
            $this->line("3. Try the result preview again");
        } else {
            $this->info("\nℹ️  All default sections already exist!");
        }

        // Show existing sections
        $existingSections = StudentGroup::all();
        if ($existingSections->count() > 0) {
            $this->line("\n📋 Available sections:");
            foreach ($existingSections as $section) {
                $this->line("  - {$section->name} (ID: {$section->id})");
            }
        }

        return 0;
    }
}
