<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'role_id',
        'password_reset_token',
        'password_reset_expires_at',
        'failed_login_attempts',
        'last_failed_login_at',
        'locked_until',
        'lockout_count',
        'last_lockout_at',
        'is_permanently_locked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'password_reset_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_reset_expires_at' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_lockout_at' => 'datetime',
            'is_permanently_locked' => 'boolean',
        ];
    }
    
    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            $user->verification_code = bin2hex(random_bytes(16));
        });
    }

    /**
     * Check if the user is locked out
     *
     * @return bool
     */
    public function isLockedOut(): bool
    {
        if ($this->is_permanently_locked) {
            return true;
        }

        if ($this->locked_until && now()->lt($this->locked_until)) {
            return true;
        }

        return false;
    }

    /**
     * Increment failed login attempts and check if user should be locked
     *
     * @return bool Whether the user is now locked
     */
    public function registerFailedLoginAttempt(): bool
    {
        $maxAttempts = (int) env('MAX_LOGIN_ATTEMPTS', 3);
        $attemptWindow = (int) env('LOGIN_ATTEMPTS_WINDOW_MINUTES', 5);
        $lockoutDuration = (int) env('ACCOUNT_LOCKOUT_DURATION_MINUTES', 60);
        $maxLockouts = (int) env('MAX_LOCKOUTS_IN_PERIOD', 2);
        $lockoutPeriod = (int) env('LOCKOUT_PERIOD_HOURS', 24);

        // If this is a new series of failed attempts or the window has expired, reset the counter
        if (!$this->last_failed_login_at || 
            now()->diffInMinutes($this->last_failed_login_at) > $attemptWindow) {
            $this->failed_login_attempts = 1;
            $this->last_failed_login_at = now();
            $this->save();
            return false;
        }

        // Increment failed attempts
        $this->failed_login_attempts++;
        $this->last_failed_login_at = now();
        
        // Check if we should lock the account
        if ($this->failed_login_attempts >= $maxAttempts) {
            // Reset failed attempts counter
            $this->failed_login_attempts = 0;
            
            // Set temporary lockout
            $this->locked_until = now()->addMinutes($lockoutDuration);
            
            // Check previous lockouts within the period
            $this->lockout_count++;
            
            // If this is the first lockout or the lockout period has expired, reset the counter
            if (!$this->last_lockout_at || 
                now()->diffInHours($this->last_lockout_at) > $lockoutPeriod) {
                $this->lockout_count = 1;
            }
            
            // Check if we've hit the maximum lockouts in the period
            if ($this->lockout_count >= $maxLockouts) {
                $this->is_permanently_locked = true;
            }
            
            $this->last_lockout_at = now();
            $this->save();
            return true;
        }
        
        $this->save();
        return false;
    }

    /**
     * Reset failed login attempts
     *
     * @return void
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->last_failed_login_at = null;
        $this->save();
    }

    /**
     * Unlock the user account
     *
     * @param bool $resetLockoutCount Whether to reset the lockout count
     * @return void
     */
    public function unlock(bool $resetLockoutCount = true): void
    {
        $this->locked_until = null;
        $this->is_permanently_locked = false;
        
        if ($resetLockoutCount) {
            $this->lockout_count = 0;
            $this->last_lockout_at = null;
        }
        
        $this->save();
    }
}
