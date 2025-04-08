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
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users Users assigned to this role
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 *
 * @OA\Schema(
 *     schema="Role",
 *     title="Role",
 *     description="User role model with unique case-insensitive name validation",
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         example=1,
 *         description="Unique identifier for the role"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="admin",
 *         description="Role name (must be unique, case-insensitive, automatically converted to lowercase)"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the role was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the role was last updated"
 *     )
 * )
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
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this role is an admin role.
     */
    public function isAdmin(): bool
    {
        return strtolower($this->name) === 'admin';
    }

    /**
     * Find a role by its name (case insensitive).
     */
    public static function findByName(string $name): ?self
    {
        return self::where('name', strtolower($name))->first();
    }

    /**
     * Setup model events to convert name to lowercase when saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($role) {
            $role->name = strtolower($role->name);
        });
    }
}
