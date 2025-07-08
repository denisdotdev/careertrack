<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Get company from route parameter or request
        $companyId = $request->route('company') ?? $request->input('company_id');
        
        if (!$companyId) {
            abort(400, 'Company ID is required');
        }

        $company = Company::findOrFail($companyId);

        // Check if user is a member of the company
        if (!$user->companies()->where('company_id', $company->id)->where('is_active', true)->exists()) {
            abort(403, 'You are not a member of this company');
        }

        // If a specific role is required, check it
        if ($role) {
            if (!$user->hasRoleInCompany($company, $role)) {
                abort(403, "You don't have the required role ({$role}) in this company");
            }
        }

        // Add company to request for use in controllers
        $request->attributes->set('company', $company);

        return $next($request);
    }
}
