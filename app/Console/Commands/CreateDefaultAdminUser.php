<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Create Default Admin User Command.
 *
 * This command ensures there is at least one admin user in the system.
 * It is intended to be run during production deployment to ensure
 * administrative access to the application is possible.
 */
class CreateDefaultAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-default-admin 
                            {--email=admin@example.com : The email address for the admin user}
                            {--password= : The password for the admin user (generated if not provided)}
                            {--force : Overwrite the admin user if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default admin user if none exists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $force = $this->option('force');

        // Check if admin role exists
        $adminRole = Role::where('name', 'admin')->first();
        if (! $adminRole) {
            $adminRole = new Role();
            $adminRole->name = 'admin';
            $adminRole->save();
            $this->info('Created admin role');
        }

        // Check if admin user exists
        $adminUser = User::where('email', $email)->first();

        if ($adminUser && ! $force) {
            $this->info("Admin user with email {$email} already exists. Use --force to overwrite.");

            return 0;
        }

        // Generate a strong password if not provided
        if (! $password) {
            $password = Str::password(12, true, true, true, false);
        }

        // Create or update the admin user
        if ($adminUser) {
            $adminUser->password = bcrypt($password);
            $adminUser->email_verified_at = now();
            $adminUser->role_id = $adminRole->id;
            $adminUser->save();
            $this->info("Updated existing admin user with email: {$email}");
        } else {
            $adminUser = new User();
            $adminUser->name = 'Admin';
            $adminUser->last_name = 'User';
            $adminUser->email = $email;
            $adminUser->password = bcrypt($password);
            $adminUser->email_verified_at = now(); // Mark as verified
            $adminUser->role_id = $adminRole->id;
            $adminUser->save();
            $this->info("Created new admin user with email: {$email}");
        }

        // Display the password only if it was generated
        if (! $this->option('password')) {
            $this->info("Generated password: {$password}");
            $this->warn('Please change this password after first login!');
        }

        return 0;
    }
}
