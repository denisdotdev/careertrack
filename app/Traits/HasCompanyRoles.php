<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\User;

trait HasCompanyRoles
{
    /**
     * Check if the authenticated user has a specific role in a company
     */
    protected function userHasRoleInCompany(Company $company, string $role): bool
    {
        $user = auth()->user();
        return $user && $user->hasRoleInCompany($company, $role);
    }

    /**
     * Check if the authenticated user is admin in a company
     */
    protected function userIsAdminInCompany(Company $company): bool
    {
        $user = auth()->user();
        return $user && $user->isAdminInCompany($company);
    }

    /**
     * Check if the authenticated user is manager in a company
     */
    protected function userIsManagerInCompany(Company $company): bool
    {
        $user = auth()->user();
        return $user && $user->isManagerInCompany($company);
    }

    /**
     * Check if the authenticated user is admin or manager in a company
     */
    protected function userIsAdminOrManagerInCompany(Company $company): bool
    {
        $user = auth()->user();
        return $user && $user->isAdminOrManagerInCompany($company);
    }

    /**
     * Get the authenticated user's role in a company
     */
    protected function getUserRoleInCompany(Company $company): ?string
    {
        $user = auth()->user();
        return $user ? $user->getRoleInCompany($company) : null;
    }

    /**
     * Authorize that the user has a specific role in the company
     */
    protected function authorizeRoleInCompany(Company $company, string $role): void
    {
        if (!$this->userHasRoleInCompany($company, $role)) {
            abort(403, "You don't have the required role ({$role}) in this company");
        }
    }

    /**
     * Authorize that the user is admin in the company
     */
    protected function authorizeAdminInCompany(Company $company): void
    {
        if (!$this->userIsAdminInCompany($company)) {
            abort(403, 'You must be an admin to perform this action');
        }
    }

    /**
     * Authorize that the user is admin or manager in the company
     */
    protected function authorizeAdminOrManagerInCompany(Company $company): void
    {
        if (!$this->userIsAdminOrManagerInCompany($company)) {
            abort(403, 'You must be an admin or manager to perform this action');
        }
    }

    /**
     * Check if a user can perform an action based on their role
     */
    protected function canPerformAction(Company $company, string $action): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $role = $user->getRoleInCompany($company);
        
        return match ($action) {
            'create_announcement' => in_array($role, ['admin', 'manager']),
            'edit_announcement' => in_array($role, ['admin', 'manager']),
            'delete_announcement' => $role === 'admin',
            'manage_users' => $role === 'admin',
            'view_analytics' => in_array($role, ['admin', 'manager']),
            'manage_company_settings' => $role === 'admin',
            'create_survey' => in_array($role, ['admin', 'manager']),
            'manage_surveys' => in_array($role, ['admin', 'manager']),
            'view_survey_results' => in_array($role, ['admin', 'manager']),
            default => false,
        };
    }
} 