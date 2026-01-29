<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = public_path('employees.csv');

        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found at: ' . $csvFile);
            return;
        }

        $file = fopen($csvFile, 'r');

        // Skip the header row
        $header = fgetcsv($file);

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            // Skip empty rows
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $name = trim($row[0]);
            $email = trim($row[1]);

            // Skip if email is empty
            if (empty($email)) {
                $skipped++;
                continue;
            }

            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $this->command->warn("User already exists: {$email}");
                $skipped++;
                continue;
            }

            // Generate password from email (everything before @ + '1234')
            $emailUsername = explode('@', $email)[0];
            $password = $emailUsername . '@1234';

            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'agent',
            ]);

            // Assign agent role using Spatie Permission
            $user->assignRole('agent');

            $imported++;
            $this->command->info("Imported: {$name} ({$email}) - Password: {$password}");
        }

        fclose($file);

        $this->command->info("\nImport completed!");
        $this->command->info("Total imported: {$imported}");
        $this->command->info("Total skipped: {$skipped}");
    }
}
