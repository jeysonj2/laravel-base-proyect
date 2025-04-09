<?php

namespace Tests\Unit\Mail;

use App\Mail\AccountLocked;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountLockedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function account_locked_mail_has_correct_data_for_temporary_lock()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addHour(),
        ]);

        // Get the lockout duration from the environment variable
        $lockoutDuration = (int) env('ACCOUNT_LOCKOUT_DURATION_MINUTES', 60);

        // Create the mailable with temporary lock
        $mailable = new AccountLocked($user, false, 60);

        // Get the pluralized word for minute
        $minuteWord = Str::plural('minute', $lockoutDuration);

        // Get rendered content for better debugging
        $html = $mailable->render();

        // Assert mailable content using more flexible assertions
        $mailable->assertHasTo($user->email);
        $mailable->assertSeeInHtml($user->name);
        $mailable->assertSeeInHtml('Your Account Has Been Temporarily Locked');

        // Check parts of the expected text separately
        $this->assertStringContainsString('automatically unlocked', $html);
        $this->assertStringContainsString((string) $lockoutDuration, $html);
        $this->assertStringContainsString($minuteWord, haystack: $html);
    }

    #[Test]
    public function account_locked_mail_has_correct_data_for_permanent_lock()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addYears(10), // Very far in the future = permanent
        ]);

        // Create the mailable with permanent lock
        $mailable = new AccountLocked($user, true);

        // Assert mailable content
        $mailable->assertHasTo($user->email);
        $mailable->assertSeeInHtml($user->name);
        $mailable->assertSeeInHtml('Your Account Has Been Permanently Locked');
        $mailable->assertSeeInHtml('contact an administrator');
    }

    #[Test]
    public function account_locked_has_correct_subject_for_temporary_lock()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addHour(),
        ]);

        // Create the mailable
        $mailable = new AccountLocked($user, false);

        // Assert subject
        $mailable->assertHasSubject('Your Account Has Been Temporarily Locked');
    }

    #[Test]
    public function account_locked_has_correct_subject_for_permanent_lock()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addYears(10),
        ]);

        // Create the mailable
        $mailable = new AccountLocked($user, true);

        // Assert subject
        $mailable->assertHasSubject('Your Account Has Been Permanently Locked');
    }
}
