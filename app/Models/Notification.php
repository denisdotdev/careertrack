<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'title',
        'message',
        'data',
        'status',
        'read_at',
        'dismissed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
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
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeDismissed($query)
    {
        return $query->where('status', 'dismissed');
    }

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
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Methods
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): void
    {
        $this->update([
            'status' => 'unread',
            'read_at' => null,
        ]);
    }

    public function dismiss(): void
    {
        $this->update([
            'status' => 'dismissed',
            'dismissed_at' => now(),
        ]);
    }

    public function isUnread(): bool
    {
        return $this->status === 'unread';
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    public function getData(string $key = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    // Static methods for creating notifications
    public static function createSurveyAvailableNotification(User $user, Company $company, Survey $survey): self
    {
        return self::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'type' => 'survey_available',
            'title' => 'New Survey Available',
            'message' => "A new survey '{$survey->title}' is now available in {$company->name}. Please take a moment to complete it.",
            'data' => [
                'survey_id' => $survey->id,
                'survey_title' => $survey->title,
                'company_name' => $company->name,
            ],
        ]);
    }

    public static function createAnnouncementNotification(User $user, Company $company, Announcement $announcement): self
    {
        return self::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'type' => 'announcement',
            'title' => 'New Announcement',
            'message' => "New announcement: {$announcement->title}",
            'data' => [
                'announcement_id' => $announcement->id,
                'announcement_title' => $announcement->title,
                'company_name' => $company->name,
            ],
        ]);
    }

    public static function createLocationAssignmentNotification(User $user, Company $company, Location $location): self
    {
        return self::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'type' => 'location_assignment',
            'title' => 'Location Assignment',
            'message' => "You have been assigned to {$location->name} in {$company->name}.",
            'data' => [
                'location_id' => $location->id,
                'location_name' => $location->name,
                'company_name' => $company->name,
            ],
        ]);
    }

    public static function createGoalUpdateNotification(User $user, Company $company, Goal $goal): self
    {
        return self::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'type' => 'goal_update',
            'title' => 'Goal Update',
            'message' => "Goal '{$goal->title}' has been updated in {$company->name}.",
            'data' => [
                'goal_id' => $goal->id,
                'goal_title' => $goal->title,
                'company_name' => $company->name,
            ],
        ]);
    }
}
