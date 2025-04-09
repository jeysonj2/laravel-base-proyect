<?php

namespace Tests\Unit\Mail;

use App\Mail\PasswordChanged;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordChangedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function password_changed_mail_has_correct_data()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Create the mailable
        $mailable = new PasswordChanged($user);

        // Assert mailable content
        $mailable->assertHasTo($user->email);
        $mailable->assertSeeInHtml($user->name);
        $mailable->assertSeeInHtml('Password Changed Successfully');
    }

    #[Test]
    public function password_changed_has_correct_subject()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Create the mailable
        $mailable = new PasswordChanged($user);

        // Assert subject
        $mailable->assertHasSubject('Password Changed Successfully');
    }

    #[Test]
    public function password_changed_contains_contact_support_information()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Create the mailable
        $mailable = new PasswordChanged($user);

        // Get rendered content
        $html = $mailable->render();

        // Assert support information is in the email
        $this->assertStringContainsString('If you did not change your password', $html);
        $this->assertStringContainsString('contact', $html);
    }
}
