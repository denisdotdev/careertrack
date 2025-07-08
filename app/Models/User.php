<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    // Relationships

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
                    ->withPivot('role', 'is_active', 'joined_at')
                    ->withTimestamps();
    }

    // Role-based methods

    /**
     * Check if user has a specific role in a company
     */
    public function hasRoleInCompany(Company $company, string $role): bool
    {
        return $this->companies()
                    ->where('company_id', $company->id)
                    ->where('role', $role)
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Check if user is admin in a company
     */
    public function isAdminInCompany(Company $company): bool
    {
        return $this->hasRoleInCompany($company, 'admin');
    }

    /**
     * Check if user is manager in a company
     */
    public function isManagerInCompany(Company $company): bool
    {
        return $this->hasRoleInCompany($company, 'manager');
    }

    /**
     * Check if user has admin or manager role in a company
     */
    public function isAdminOrManagerInCompany(Company $company): bool
    {
        return $this->companies()
                    ->where('company_id', $company->id)
                    ->whereIn('role', ['admin', 'manager'])
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Get user's role in a specific company
     */
    public function getRoleInCompany(Company $company): ?string
    {
        $pivot = $this->companies()
                      ->where('company_id', $company->id)
                      ->where('is_active', true)
                      ->first()?->pivot;

        return $pivot?->role;
    }

    /**
     * Add user to a company with a specific role
     */
    public function addToCompany(Company $company, string $role = 'member'): void
    {
        $this->companies()->attach($company->id, [
            'role' => $role,
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove user from a company
     */
    public function removeFromCompany(Company $company): void
    {
        $this->companies()->detach($company->id);
    }

    /**
     * Update user's role in a company
     */
    public function updateRoleInCompany(Company $company, string $role): void
    {
        $this->companies()->updateExistingPivot($company->id, [
            'role' => $role,
        ]);
    }
}
