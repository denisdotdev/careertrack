<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'notification_type',
        'email_enabled',
        'in_app_enabled',
        'push_enabled',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
        'push_enabled' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForCompany($query, Company $company)
    {
        return $query->where('company_id', $company->id);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    public function scopeEmailEnabled($query)
    {
        return $query->where('email_enabled', true);
    }

    public function scopeInAppEnabled($query)
    {
        return $query->where('in_app_enabled', true);
    }

    public function scopePushEnabled($query)
    {
        return $query->where('push_enabled', true);
    }

    // Methods
    public function isEmailEnabled(): bool
    {
        return $this->email_enabled;
    }

    public function isInAppEnabled(): bool
    {
        return $this->in_app_enabled;
    }

    public function isPushEnabled(): bool
    {
        return $this->push_enabled;
    }

    public function enableEmail(): void
    {
        $this->update(['email_enabled' => true]);
    }

    public function disableEmail(): void
    {
        $this->update(['email_enabled' => false]);
    }

    public function enableInApp(): void
    {
        $this->update(['in_app_enabled' => true]);
    }

    public function disableInApp(): void
    {
        $this->update(['in_app_enabled' => false]);
    }

    public function enablePush(): void
    {
        $this->update(['push_enabled' => true]);
    }

    public function disablePush(): void
    {
        $this->update(['push_enabled' => false]);
    }

    public function enableAll(): void
    {
        $this->update([
            'email_enabled' => true,
            'in_app_enabled' => true,
            'push_enabled' => true,
        ]);
    }

    public function disableAll(): void
    {
        $this->update([
            'email_enabled' => false,
            'in_app_enabled' => false,
            'push_enabled' => false,
        ]);
    }

    // Static methods
    public static function getOrCreate(User $user, Company $company, string $type): self
    {
        return self::firstOrCreate(
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'notification_type' => $type,
            ],
            [
                'email_enabled' => true,
                'in_app_enabled' => true,
                'push_enabled' => false,
            ]
        );
    }

    public static function isEnabled(User $user, Company $company, string $type, string $channel = 'in_app'): bool
    {
        $preference = self::where([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'notification_type' => $type,
        ])->first();

        if (!$preference) {
            // Default to enabled for in_app and email, disabled for push
            return in_array($channel, ['in_app', 'email']);
        }

        return match ($channel) {
            'email' => $preference->isEmailEnabled(),
            'in_app' => $preference->isInAppEnabled(),
            'push' => $preference->isPushEnabled(),
            default => false,
        };
    }
}
