<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Create Default Superadmin User Command.
 *
 * This command ensures there is at least one superadmin user in the system.
 * It is intended to be run during production deployment to ensure
 * administrative access to the application is possible.
 */
class CreateDefaultSuperadminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-default-superadmin 
                            {--email=superadmin@example.com : The email address for the superadmin user}
                            {--password= : The password for the superadmin user (generated if not provided)}
                            {--force : Overwrite the superadmin user if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default superadmin user if none exists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $force = $this->option('force');

        // Check if superadmin role exists
        $superadminRole = Role::where('name', 'superadmin')->first();
        if (! $superadminRole) {
            $superadminRole = new Role();
            $superadminRole->name = 'superadmin';
            $superadminRole->save();
            $this->info('Created superadmin role');
        }

        // Check if superadmin user exists
        $superadminUser = User::where('email', $email)->first();

        if ($superadminUser && ! $force) {
            $this->info("Superadmin user with email {$email} already exists. Use --force to overwrite.");

            return 0;
        }

        // Generate a strong password if not provided
        if (! $password) {
            $password = Str::password(20, true, true, false, false);
        }

        // Create or update the superadmin user
        if ($superadminUser) {
            $superadminUser->name = 'Superadmin';
            $superadminUser->last_name = 'User';
            $superadminUser->password = bcrypt($password);
            $superadminUser->email_verified_at = now();
            $superadminUser->role_id = $superadminRole->id;
            $superadminUser->save();
            $this->info("Updated existing superadmin user with email: {$email}");
        } else {
            $superadminUser = new User();
            $superadminUser->name = 'Superadmin';
            $superadminUser->last_name = 'User';
            $superadminUser->email = $email;
            $superadminUser->password = bcrypt($password);
            $superadminUser->email_verified_at = now(); // Mark as verified
            $superadminUser->role_id = $superadminRole->id;
            $superadminUser->save();
            $this->info("Created new superadmin user with email: {$email}");
        }

        // Display the password only if it was generated
        if (! $this->option('password')) {
            $this->info("Generated password: {$password}");
            $this->warn('Please change this password after first login!');
        }

        return 0;
    }
}
