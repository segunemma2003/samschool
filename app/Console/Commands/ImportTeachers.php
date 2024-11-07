<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Shuchkin\SimpleXLSX;
use App\Models\User;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ImportTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:teachers';
    protected $description = 'Import teachers from XLSX and create user and teacher records';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Specify the database connection
        $databaseName = 'tomatophp_74337536-07e0-4590-8861-d0a37616ac8a_db';
        DB::purge('mysql'); // Reset any existing database connection
        config(['database.connections.mysql.database' => $databaseName]); // Set the new database name
        DB::reconnect('mysql'); // Reconnect with the new database configuration

        // Load XLSX file from public path
        $filePath = public_path('schools/viff.xlsx');


        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        } else {
            $this->info("File found: {$filePath}");
        }

        // Parse XLSX file using SimpleXLSX
        $xlsx = SimpleXLSX::parse($filePath);

        if (!$xlsx) {
            $this->error("Failed to parse file: " . SimpleXLSX::parseError());
            return;
        }

        // Get the second sheet (Sheet 2)
        $rows = $xlsx->rows(2); // 1 corresponds to Sheet 2, as sheet indices are 0-based
        // dd($rows);
        // Initialize a counter to track consecutive empty rows
        $consecutiveEmptyRows = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0 && empty($row[0]) && empty($row[1])) {
                continue; // Skip the header row if empty
            }

            // Map XLSX data to appropriate columns
            $data = [
                'Name' => $row[0] ?? null,
                'Designation' => $row[1] ?? null,
                'Dob' => $row[2] ?? null,
                'Gender' => $row[3] ?? null,
                'Religion' => $row[4] ?? null,
                'Email' => $row[5] ?? null,
                'Phone' => $row[6] ?? null,
                'Address' => $row[7] ?? null,
                'Jod' => $row[8] ?? null,
            ];

            // Log the data for verification
            $this->info("Processing row: " . json_encode($data));

            // Check if both 'Email' and 'Name' are empty, then skip the row
            if (empty($data['Email']) && empty($data['Name'])) {
                $consecutiveEmptyRows++;
                // Stop the loop if 2 consecutive empty rows are encountered
                if ($consecutiveEmptyRows >= 2) {
                    $this->info("Two consecutive empty rows found. Stopping import.");
                    break;
                }
                continue; // Skip the current empty row
            } else {
                $consecutiveEmptyRows = 0; // Reset the counter when a valid row is encountered
            }

            // Prepare the email and username
            $email = strtolower($data['Email']);
            $username = explode('@', $email)[0];

            // Capitalize the first letter of religion
            $religion = ucfirst(strtolower($data['Religion']));

            // Create or update User record
            $user = User::updateOrCreate([
                'email' => $email,
            ], [
                'name' => $data['Name'],
                'email' => $email,
                'password' => Hash::make('Teacher@12345'),
                'username' => $username,
                'user_type' => 'teacher',
            ]);

            // Handle 'Dob' in d/m/y format, use current date if empty
            $dob = empty($data['Dob']) ? Carbon::now()->toDateString() : $data['Dob'];
            if ($dob) {
                try {
                    $dob = Carbon::createFromFormat('d/m/Y', $dob)->toDateString();
                } catch (\Exception $e) {
                    $dob = Carbon::now()->toDateString(); // If the date format is invalid, use current date
                }
            }

            // Handle 'Jod' (Joining Date) in d/m/y format, use current date if empty
            $joiningDate = empty($data['Jod']) ? Carbon::now()->toDateString() : $data['Jod'];
            if ($joiningDate) {
                try {
                    $joiningDate = Carbon::createFromFormat('d/m/Y', $joiningDate)->toDateString();
                } catch (\Exception $e) {
                    $joiningDate = Carbon::now()->toDateString(); // If the date format is invalid, use current date
                }
            }

            // Log the processed values
            $this->info("Processed Dob: {$dob}, Joining Date: {$joiningDate}");

            // Create Teacher record
            Teacher::updateOrCreate([
                'email' => $email,
            ], [
                'name' => $data['Name'],
                'email' => $email,
                'designation' => $data['Designation'],
                'date_of_birth' => $dob,
                'gender' => strtolower($data['Gender']), // Convert "MALE" to "male" and "FEMALE" to "female"
                'religion' => $religion,
                'joining_date' => $joiningDate, // The validated joining date
                'username' => $username,
                'phone' => $data['Phone'],
                'address' => $data['Address'],
                'password' => Hash::make('Teacher@12345'),
            ]);
        }

        $this->info("Teachers imported successfully.");
    }
}
