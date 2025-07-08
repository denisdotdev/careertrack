<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_id',
        'street_address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'description',
        'is_active',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'location_user')
                    ->withPivot('is_primary', 'assigned_at')
                    ->withTimestamps();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, Company $company)
    {
        return $query->where('company_id', $company->id);
    }

    // Methods

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->street_address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }

        return null;
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function addUser(User $user, bool $isPrimary = false): void
    {
        // If this is set as primary, remove primary from other locations for this user
        if ($isPrimary) {
            $user->locations()->updateExistingPivot($user->locations->pluck('id'), ['is_primary' => false]);
        }

        $this->users()->attach($user->id, [
            'is_primary' => $isPrimary,
            'assigned_at' => now(),
        ]);
    }

    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    public function updateUserPrimary(User $user, bool $isPrimary): void
    {
        if ($isPrimary) {
            // Remove primary from other locations for this user
            $user->locations()->updateExistingPivot($user->locations->pluck('id'), ['is_primary' => false]);
        }

        $this->users()->updateExistingPivot($user->id, [
            'is_primary' => $isPrimary,
        ]);
    }

    public function getPrimaryUsers()
    {
        return $this->users()->wherePivot('is_primary', true);
    }

    public function isUserPrimary(User $user): bool
    {
        return $this->users()
                    ->where('user_id', $user->id)
                    ->wherePivot('is_primary', true)
                    ->exists();
    }
}