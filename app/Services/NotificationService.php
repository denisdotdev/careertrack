<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Notification;
use App\Models\Survey;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send survey available notifications to all eligible users in a company
     */
    public function sendSurveyAvailableNotifications(Survey $survey): void
    {
        $company = $survey->company;
        $eligibleUsers = $company->users()
            ->whereHas('notificationPreferences', function ($query) {
                $query->where('notification_type', 'survey_available')
                      ->where('in_app_enabled', true);
            })
            ->orWhereDoesntHave('notificationPreferences', function ($query) {
                $query->where('notification_type', 'survey_available');
            })
            ->get();

        foreach ($eligibleUsers as $user) {
            if ($this->shouldSendNotification($user, $company, 'survey_available')) {
                try {
                    Notification::createSurveyAvailableNotification($user, $company, $survey);
                    Log::info("Survey notification sent to user {$user->id} for survey {$survey->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send survey notification to user {$user->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Send announcement notifications to all eligible users in a company
     */
    public function sendAnnouncementNotifications($announcement): void
    {
        $company = $announcement->company;
        $eligibleUsers = $company->users()
            ->whereHas('notificationPreferences', function ($query) {
                $query->where('notification_type', 'announcement')
                      ->where('in_app_enabled', true);
            })
            ->orWhereDoesntHave('notificationPreferences', function ($query) {
                $query->where('notification_type', 'announcement');
            })
            ->get();

        foreach ($eligibleUsers as $user) {
            if ($this->shouldSendNotification($user, $company, 'announcement')) {
                try {
                    Notification::createAnnouncementNotification($user, $company, $announcement);
                    Log::info("Announcement notification sent to user {$user->id} for announcement {$announcement->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send announcement notification to user {$user->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Send location assignment notification to a specific user
     */
    public function sendLocationAssignmentNotification(User $user, Company $company, $location): void
    {
        if ($this->shouldSendNotification($user, $company, 'location_assignment')) {
            try {
                Notification::createLocationAssignmentNotification($user, $company, $location);
                Log::info("Location assignment notification sent to user {$user->id} for location {$location->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send location assignment notification to user {$user->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Send goal update notification to all eligible users in a company
     */
    public function sendGoalUpdateNotifications($goal): void
    {
        $company = $goal->company;
        $eligibleUsers = $company->users()
            ->whereHas('notificationPreferences', function ($query) {
                $query->where('notification_type', 'goal_update')
                      ->where('in_app_enabled', true);
            })
            ->orWhereDoesntHave('notificationPreferences', function ($query) {
                $query->where('notification_type', 'goal_update');
            })
            ->get();

        foreach ($eligibleUsers as $user) {
            if ($this->shouldSendNotification($user, $company, 'goal_update')) {
                try {
                    Notification::createGoalUpdateNotification($user, $company, $goal);
                    Log::info("Goal update notification sent to user {$user->id} for goal {$goal->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send goal update notification to user {$user->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Check if a user should receive a notification based on their preferences
     */
    private function shouldSendNotification(User $user, Company $company, string $type): bool
    {
        return UserNotificationPreference::isEnabled($user, $company, $type, 'in_app');
    }

    /**
     * Get notification statistics for a user
     */
    public function getUserNotificationStats(User $user): array
    {
        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->getUnreadNotificationsCount(),
            'read' => $user->notifications()->read()->count(),
            'dismissed' => $user->notifications()->dismissed()->count(),
            'recent' => $user->notifications()->recent(7)->count(),
        ];
    }

    /**
     * Get notification statistics for a company
     */
    public function getCompanyNotificationStats(Company $company): array
    {
        return [
            'total' => $company->notifications()->count(),
            'unread' => $company->notifications()->unread()->count(),
            'read' => $company->notifications()->read()->count(),
            'dismissed' => $company->notifications()->dismissed()->count(),
            'recent' => $company->notifications()->recent(7)->count(),
            'by_type' => $company->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }

    /**
     * Clean up old notifications (older than specified days)
     */
    public function cleanupOldNotifications(int $days = 90): int
    {
        $deletedCount = Notification::where('created_at', '<', now()->subDays($days))
            ->whereIn('status', ['read', 'dismissed'])
            ->delete();

        Log::info("Cleaned up {$deletedCount} old notifications");
        return $deletedCount;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $user->markAllNotificationsAsRead();
        Log::info("Marked all notifications as read for user {$user->id}");
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
        Log::info("Marked notification {$notification->id} as read");
    }

    /**
     * Dismiss a notification
     */
    public function dismissNotification(Notification $notification): void
    {
        $notification->dismiss();
        Log::info("Dismissed notification {$notification->id}");
    }
} 