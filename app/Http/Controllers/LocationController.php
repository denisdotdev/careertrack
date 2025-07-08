<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Traits\HasCompanyRoles;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    use HasCompanyRoles;

    /**
     * Display a listing of locations for a company.
     */
    public function index(Request $request, Company $company): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $locations = $company->locations()
            ->with(['users' => function ($query) {
                $query->select('users.id', 'name', 'email');
            }])
            ->when($request->boolean('active_only'), function ($query) {
                $query->active();
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'locations' => $locations,
            'total' => $locations->count(),
        ]);
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request, Company $company): JsonResponse
    {
        // Only admins and managers can create locations
        if (!$this->userCanManageLocations(auth()->user(), $company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'street_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $location = $company->locations()->create($validator->validated());

        return response()->json([
            'message' => 'Location created successfully',
            'location' => $location->load('users'),
        ], 201);
    }

    /**
     * Display the specified location.
     */
    public function show(Company $company, Location $location): JsonResponse
    {
        // Check if location belongs to company
        if ($location->company_id !== $company->id) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $location->load(['users' => function ($query) {
            $query->select('users.id', 'name', 'email');
        }]);

        return response()->json([
            'location' => $location,
        ]);
    }

    /**
     * Update the specified location.
     */
    public function update(Request $request, Company $company, Location $location): JsonResponse
    {
        // Check if location belongs to company
        if ($location->company_id !== $company->id) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // Only admins and managers can update locations
        if (!$this->userCanManageLocations(auth()->user(), $company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'street_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $location->update($validator->validated());

        return response()->json([
            'message' => 'Location updated successfully',
            'location' => $location->load('users'),
        ]);
    }

    /**
     * Remove the specified location.
     */
    public function destroy(Company $company, Location $location): JsonResponse
    {
        // Check if location belongs to company
        if ($location->company_id !== $company->id) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // Only admins can delete locations
        if (!$this->userIsAdminInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        // Check if location has users assigned
        if ($location->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete location with assigned users. Please reassign users first.',
            ], 422);
        }

        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully',
        ]);
    }

    /**
     * Assign users to a location.
     */
    public function assignUsers(Request $request, Company $company, Location $location): JsonResponse
    {
        // Check if location belongs to company
        if ($location->company_id !== $company->id) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // Only admins and managers can assign users to locations
        if (!$this->userCanManageLocations(auth()->user(), $company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'set_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userIds = $request->input('user_ids');
        $setPrimary = $request->boolean('set_primary', false);

        // Verify all users belong to the company
        $companyUserIds = $company->users()->pluck('users.id')->toArray();
        $invalidUserIds = array_diff($userIds, $companyUserIds);
        
        if (!empty($invalidUserIds)) {
            return response()->json([
                'message' => 'Some users do not belong to this company',
                'invalid_user_ids' => $invalidUserIds,
            ], 422);
        }

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            $location->addUser($user, $setPrimary);
        }

        return response()->json([
            'message' => 'Users assigned to location successfully',
            'location' => $location->load('users'),
        ]);
    }

    /**
     * Remove users from a location.
     */
    public function removeUsers(Request $request, Company $company, Location $location): JsonResponse
    {
        // Check if location belongs to company
        if ($location->company_id !== $company->id) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // Only admins and managers can remove users from locations
        if (!$this->userCanManageLocations(auth()->user(), $company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userIds = $request->input('user_ids');

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            $location->removeUser($user);
        }

        return response()->json([
            'message' => 'Users removed from location successfully',
            'location' => $location->load('users'),
        ]);
    }

    /**
     * Set a location as primary for a user.
     */
    public function setPrimaryLocation(Request $request, Company $company, Location $location): JsonResponse
    {
        // Check if location belongs to company
        if ($location->company_id !== $company->id) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // Users can set their own primary location, or admins/managers can set it for others
        $user = auth()->user();
        $targetUserId = $request->input('user_id', $user->id);

        if ($targetUserId !== $user->id && !$this->userCanManageLocations($user, $company)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $targetUser = User::find($targetUserId);
        
        if (!$targetUser || !$this->userHasRoleInCompany($targetUser, $company)) {
            return response()->json(['message' => 'User not found or not in company'], 404);
        }

        if (!$location->hasUser($targetUser)) {
            return response()->json(['message' => 'User is not assigned to this location'], 422);
        }

        $location->updateUserPrimary($targetUser, true);

        return response()->json([
            'message' => 'Primary location set successfully',
            'location' => $location->load('users'),
        ]);
    }

    /**
     * Get location statistics for a company.
     */
    public function statistics(Company $company): JsonResponse
    {
        // Check if user has access to this company
        if (!$this->userHasRoleInCompany(auth()->user(), $company)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalLocations = $company->locations()->count();
        $activeLocations = $company->locations()->active()->count();
        $locationsWithUsers = $company->locations()->has('users')->count();
        $totalUsersAssigned = $company->locations()->withCount('users')->get()->sum('users_count');

        return response()->json([
            'statistics' => [
                'total_locations' => $totalLocations,
                'active_locations' => $activeLocations,
                'inactive_locations' => $totalLocations - $activeLocations,
                'locations_with_users' => $locationsWithUsers,
                'locations_without_users' => $totalLocations - $locationsWithUsers,
                'total_users_assigned' => $totalUsersAssigned,
                'average_users_per_location' => $totalLocations > 0 ? round($totalUsersAssigned / $totalLocations, 2) : 0,
            ],
        ]);
    }
}
