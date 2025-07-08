<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'website',
        'address',
        'phone',
        'email',
    ];

    // Relationships

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user')
                    ->withPivot('role', 'is_active', 'joined_at')
                    ->withTimestamps();
    }

    // Role-based methods

    /**
     * Get all users with a specific role
     */
    public function getUsersByRole(string $role)
    {
        return $this->users()->wherePivot('role', $role)->wherePivot('is_active', true);
    }

    /**
     * Get all admin users
     */
    public function getAdmins()
    {
        return $this->getUsersByRole('admin');
    }

    /**
     * Get all manager users
     */
    public function getManagers()
    {
        return $this->getUsersByRole('manager');
    }

    /**
     * Check if a user is a member of this company
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->wherePivot('is_active', true)->exists();
    }

    /**
     * Add a user to the company with a specific role
     */
    public function addUser(User $user, string $role = 'member'): void
    {
        $this->users()->attach($user->id, [
            'role' => $role,
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a user from the company
     */
    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }
}