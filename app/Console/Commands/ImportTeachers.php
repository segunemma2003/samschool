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
        $databaseName = 'tomatophp_c5529b38-53a4-48fb-8fff-57674821e54b_db';
        DB::purge('mysql'); // Reset any existing database connection
        config(['database.connections.mysql.database' => $databaseName]); // Set the new database name
        DB::reconnect('mysql'); // Reconnect with the new database configuration

        // Load XLSX file from public path
        $filePath = public_path('schools/rol.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        // Parse XLSX file using SimpleXLSX
        $xlsx = SimpleXLSX::parse($filePath);

        if (!$xlsx) {
            $this->error("Failed to parse file: " . SimpleXLSX::parseError());
            return;
        }

        // Assume the first row is the header
        $header = $xlsx->rows()[0];
        $rows = $xlsx->rows();

        // Remove the header from the rows array
        array_shift($rows);

        // Initialize a counter to track consecutive empty rows
        $consecutiveEmptyRows = 0;

        foreach ($rows as $row) {
            // Map XLSX data to header columns
            $data = array_combine($header, $row);

            // Check if both 'EMAIL' and 'NAME' are empty, then skip the row
            if (empty($data['EMAIL']) && empty($data['NAME'])) {
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
            $email = strtolower($data['EMAIL']);
            $username = explode('@', $email)[0];

            // Capitalize the first letter of religion
            $religion = ucfirst(strtolower($data['RELIGION']));

            // Create or update User record
            $user = User::updateOrCreate([
                'email' => $email,
            ], [
                'name' => $data['NAME'],
                'email' => $email,
                'password' => Hash::make('Teacher@12345'),
                'username' => $username,
                'user_type' => 'teacher',
            ]);

            // Handle 'DATE OF BIRTH' in d/m/y format, use current date if empty
            $dateOfBirth = empty($data['DATE OF BIRTH']) ? Carbon::now()->toDateString() : explode(' ', $data['DATE OF BIRTH'])[0];
            if ($dateOfBirth) {
                try {
                    $dateOfBirth = Carbon::createFromFormat('d/m/Y', $dateOfBirth)->toDateString();
                } catch (\Exception $e) {
                    $dateOfBirth = Carbon::now()->toDateString(); // If the date format is invalid, use current date
                }
            }

            // Handle 'JOINING DATE' in d/m/y format, use current date if empty
            $joiningDate = empty($data['EMPLOYMENT DATE']) ? Carbon::now()->toDateString() : $data['EMPLOYMENT DATE'];
            if ($joiningDate) {
                try {
                    $joiningDate = Carbon::createFromFormat('d/m/Y', $joiningDate)->toDateString();
                } catch (\Exception $e) {
                    $joiningDate = Carbon::now()->toDateString(); // If the date format is invalid, use current date
                }
            }

            // Create Teacher record
            Teacher::updateOrCreate([
                'email' => $email,
            ],[
                'name' => $data['NAME'],
                'email' => $email,
                'designation' => $data['DESIGNATION'],
                'date_of_birth' => $dateOfBirth,
                'gender' => strtolower($data['GENDER']), // Convert "MALE" to "male" and "FEMALE" to "female"
                'religion' => $religion,
                'joining_date' => $joiningDate, // The validated joining date
                'username' => $username,
                'phone' => $data['PHONE NO'],
                'address' => $data['ADDRESS'],
                'password' => Hash::make('Teacher@12345'),
            ]);
        }

        $this->info("Teachers imported successfully.");
    }
}
