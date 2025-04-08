<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Role model representing a user role in the system.
 * 
 * This model manages user roles which control access permissions throughout the application.
 * Each user is assigned a role that determines their access level and capabilities.
 *
 * @property int $id The unique identifier for the role
 * @property string $name The name of the role (e.g., 'admin', 'user')
 * @property \Illuminate\Support\Carbon $created_at Timestamp of when the role was created
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of when the role was last updated
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users Users assigned to this role
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the users associated with this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Check if this role is an admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return strtolower($this->name) === 'admin';
    }
    
    /**
     * Find a role by its name (case insensitive).
     *
     * @param string $name
     * @return self|null
     */
    public static function findByName(string $name): ?self
    {
        return self::where('name', strtolower($name))->first();
    }
    
    /**
     * Setup model events to convert name to lowercase when saving.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($role) {
            $role->name = strtolower($role->name);
        });
    }
}
