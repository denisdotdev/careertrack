<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Notification;
use App\Models\UserNotificationPreference;
use App\Services\NotificationService;
use App\Traits\HasCompanyRoles;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use HasCompanyRoles;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's notifications for a company
     */
    public function index(Request $request, Company $company): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = auth()->user();
        $query = $user->notifications()->forCompany($company);

        // Filter by status
        if ($request->has('status')) {
            $status = $request->input('status');
            if (in_array($status, ['unread', 'read', 'dismissed'])) {
                $query = $query->where('status', $status);
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query = $query->ofType($request->input('type'));
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'notifications' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'stats' => $this->notificationService->getUserNotificationStats($user),
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Company $company): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = auth()->user();
        $count = $user->notifications()
            ->forCompany($company)
            ->unread()
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Company $company, Notification $notification): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if notification belongs to the user and company
        if ($notification->user_id !== auth()->id() || $notification->company_id !== $company->id) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification->fresh(),
        ]);
    }

    /**
     * Mark all notifications as read for a company
     */
    public function markAllAsRead(Company $company): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = auth()->user();
        $this->notificationService->markAllAsRead($user);

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Dismiss a notification
     */
    public function dismiss(Company $company, Notification $notification): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if notification belongs to the user and company
        if ($notification->user_id !== auth()->id() || $notification->company_id !== $company->id) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $this->notificationService->dismissNotification($notification);

        return response()->json([
            'message' => 'Notification dismissed',
            'notification' => $notification->fresh(),
        ]);
    }

    /**
     * Get user's notification preferences for a company
     */
    public function getPreferences(Company $company): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = auth()->user();
        $preferences = $user->notificationPreferences()
            ->forCompany($company)
            ->get()
            ->keyBy('notification_type');

        // Define available notification types
        $notificationTypes = [
            'survey_available' => [
                'label' => 'New Surveys',
                'description' => 'Get notified when new surveys are available',
            ],
            'announcement' => [
                'label' => 'Announcements',
                'description' => 'Get notified about company announcements',
            ],
            'location_assignment' => [
                'label' => 'Location Assignments',
                'description' => 'Get notified when you are assigned to a location',
            ],
            'goal_update' => [
                'label' => 'Goal Updates',
                'description' => 'Get notified when company goals are updated',
            ],
        ];

        // Build response with all notification types
        $response = [];
        foreach ($notificationTypes as $type => $info) {
            $preference = $preferences->get($type);
            $response[$type] = [
                'label' => $info['label'],
                'description' => $info['description'],
                'email_enabled' => $preference ? $preference->isEmailEnabled() : true,
                'in_app_enabled' => $preference ? $preference->isInAppEnabled() : true,
                'push_enabled' => $preference ? $preference->isPushEnabled() : false,
            ];
        }

        return response()->json([
            'preferences' => $response,
        ]);
    }

    /**
     * Update user's notification preferences for a company
     */
    public function updatePreferences(Request $request, Company $company): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'preferences' => 'required|array',
            'preferences.*.type' => 'required|string|in:survey_available,announcement,location_assignment,goal_update',
            'preferences.*.email_enabled' => 'boolean',
            'preferences.*.in_app_enabled' => 'boolean',
            'preferences.*.push_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $updatedPreferences = [];

        foreach ($request->input('preferences') as $pref) {
            $preference = UserNotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'notification_type' => $pref['type'],
                ],
                [
                    'email_enabled' => $pref['email_enabled'] ?? true,
                    'in_app_enabled' => $pref['in_app_enabled'] ?? true,
                    'push_enabled' => $pref['push_enabled'] ?? false,
                ]
            );

            $updatedPreferences[] = $preference;
        }

        return response()->json([
            'message' => 'Notification preferences updated successfully',
            'preferences' => $updatedPreferences,
        ]);
    }

    /**
     * Update a specific notification preference
     */
    public function updatePreference(Request $request, Company $company, string $type): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $preference = UserNotificationPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'notification_type' => $type,
            ],
            $request->only(['email_enabled', 'in_app_enabled', 'push_enabled'])
        );

        return response()->json([
            'message' => 'Notification preference updated successfully',
            'preference' => $preference,
        ]);
    }

    /**
     * Get notification statistics for a company (admin/manager only)
     */
    public function statistics(Company $company): JsonResponse
    {
        // Only admins and managers can view company notification statistics
        if (!$this->userIsAdminOrManagerInCompany($company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $stats = $this->notificationService->getCompanyNotificationStats($company);

        return response()->json([
            'statistics' => $stats,
        ]);
    }

    /**
     * Delete old notifications (admin only)
     */
    public function cleanup(Request $request, Company $company): JsonResponse
    {
        // Only admins can cleanup notifications
        if (!$this->userIsAdminInCompany($company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $days = $request->input('days', 90);
        $deletedCount = $this->notificationService->cleanupOldNotifications($days);

        return response()->json([
            'message' => "Cleaned up {$deletedCount} old notifications",
            'deleted_count' => $deletedCount,
        ]);
    }
}
