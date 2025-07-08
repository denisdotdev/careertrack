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

    public function createdSurveys()
    {
        return $this->hasMany(Survey::class, 'created_by');
    }

    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_user')
                    ->withPivot('is_primary', 'assigned_at')
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

    // Location management methods

    /**
     * Add user to a location
     */
    public function addToLocation(Location $location, bool $isPrimary = false): void
    {
        $this->locations()->attach($location->id, [
            'is_primary' => $isPrimary,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Remove user from a location
     */
    public function removeFromLocation(Location $location): void
    {
        $this->locations()->detach($location->id);
    }

    /**
     * Get user's primary location
     */
    public function getPrimaryLocation()
    {
        return $this->locations()->wherePivot('is_primary', true)->first();
    }

    /**
     * Check if user has a primary location
     */
    public function hasPrimaryLocation(): bool
    {
        return $this->locations()->wherePivot('is_primary', true)->exists();
    }

    /**
     * Set a location as primary for the user
     */
    public function setPrimaryLocation(Location $location): void
    {
        // Remove primary from all other locations
        $this->locations()->updateExistingPivot($this->locations->pluck('id'), ['is_primary' => false]);
        
        // Set this location as primary
        $this->locations()->updateExistingPivot($location->id, ['is_primary' => true]);
    }

    /**
     * Get all locations for a specific company
     */
    public function getLocationsForCompany(Company $company)
    {
        return $this->locations()->where('company_id', $company->id);
    }

    // Notification relationships
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreferences()
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    // Notification methods
    public function getUnreadNotifications()
    {
        return $this->notifications()->unread()->orderBy('created_at', 'desc');
    }

    public function getUnreadNotificationsCount(): int
    {
        return $this->notifications()->unread()->count();
    }

    public function markAllNotificationsAsRead(): void
    {
        $this->notifications()->unread()->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function getNotificationPreference(Company $company, string $type): ?UserNotificationPreference
    {
        return $this->notificationPreferences()
                    ->where('company_id', $company->id)
                    ->where('notification_type', $type)
                    ->first();
    }

    public function setNotificationPreference(Company $company, string $type, array $settings): UserNotificationPreference
    {
        return UserNotificationPreference::updateOrCreate(
            [
                'user_id' => $this->id,
                'company_id' => $company->id,
                'notification_type' => $type,
            ],
            $settings
        );
    }

    public function shouldReceiveNotification(Company $company, string $type, string $channel = 'in_app'): bool
    {
        return UserNotificationPreference::isEnabled($this, $company, $type, $channel);
    }
}
