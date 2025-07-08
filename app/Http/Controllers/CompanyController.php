<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Traits\HasCompanyRoles;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use HasCompanyRoles;

    /**
     * Display company dashboard
     */
    public function dashboard(Company $company)
    {
        // Check if user is a member of the company
        if (!auth()->user()->companies()->where('company_id', $company->id)->exists()) {
            abort(403, 'You are not a member of this company');
        }

        $user = auth()->user();
        $role = $user->getRoleInCompany($company);
        $canManageUsers = $this->canPerformAction($company, 'manage_users');
        $canCreateAnnouncements = $this->canPerformAction($company, 'create_announcement');

        return response()->json([
            'company' => $company,
            'user_role' => $role,
            'permissions' => [
                'can_manage_users' => $canManageUsers,
                'can_create_announcements' => $canCreateAnnouncements,
                'can_view_analytics' => $this->canPerformAction($company, 'view_analytics'),
                'can_manage_settings' => $this->canPerformAction($company, 'manage_company_settings'),
            ],
            'announcements' => $company->announcements()->latest()->take(5)->get(),
            'users' => $company->users()->with('pivot')->get(),
        ]);
    }

    /**
     * Add a user to the company (Admin only)
     */
    public function addUser(Request $request, Company $company)
    {
        $this->authorizeAdminInCompany($company);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,manager,member,viewer',
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Check if user is already a member
        if ($company->hasUser($user)) {
            return response()->json(['message' => 'User is already a member of this company'], 400);
        }

        $company->addUser($user, $request->role);

        return response()->json([
            'message' => 'User added successfully',
            'user' => $user,
            'role' => $request->role,
        ]);
    }

    /**
     * Update user role in company (Admin only)
     */
    public function updateUserRole(Request $request, Company $company, User $user)
    {
        $this->authorizeAdminInCompany($company);

        $request->validate([
            'role' => 'required|in:admin,manager,member,viewer',
        ]);

        // Check if user is a member of the company
        if (!$company->hasUser($user)) {
            return response()->json(['message' => 'User is not a member of this company'], 400);
        }

        $user->updateRoleInCompany($company, $request->role);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user,
            'new_role' => $request->role,
        ]);
    }

    /**
     * Remove user from company (Admin only)
     */
    public function removeUser(Company $company, User $user)
    {
        $this->authorizeAdminInCompany($company);

        // Check if user is a member of the company
        if (!$company->hasUser($user)) {
            return response()->json(['message' => 'User is not a member of this company'], 400);
        }

        $company->removeUser($user);

        return response()->json([
            'message' => 'User removed from company successfully',
        ]);
    }

    /**
     * Get company users by role
     */
    public function getUsersByRole(Company $company, string $role)
    {
        // Only admins and managers can view user lists
        $this->authorizeAdminOrManagerInCompany($company);

        $users = $company->getUsersByRole($role)->get();

        return response()->json([
            'role' => $role,
            'users' => $users,
            'count' => $users->count(),
        ]);
    }

    /**
     * Get user's companies and roles
     */
    public function myCompanies()
    {
        $user = auth()->user();
        $companies = $user->companies()->with('pivot')->get();

        return response()->json([
            'companies' => $companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'role' => $company->pivot->role,
                    'joined_at' => $company->pivot->joined_at,
                    'is_active' => $company->pivot->is_active,
                ];
            }),
        ]);
    }
}
