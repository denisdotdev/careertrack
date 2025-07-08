<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'company_id',
        'created_by',
        'status',
        'start_date',
        'end_date',
        'is_anonymous',
        'allow_multiple_responses',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_anonymous' => 'boolean',
        'allow_multiple_responses' => 'boolean',
    ];

    // Relationships

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->ordered();
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeForCompany($query, Company $company)
    {
        return $query->where('company_id', $company->id);
    }

    public function scopeByCreator($query, User $user)
    {
        return $query->where('created_by', $user->id);
    }

    // Methods

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isExpired()
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function canBeFilledBy(User $user)
    {
        // Check if user is a member of the company
        if (!$this->company->hasUser($user)) {
            return false;
        }

        // Check if survey is active
        if (!$this->isActive()) {
            return false;
        }

        // Check if survey has expired
        if ($this->isExpired()) {
            return false;
        }

        // Check if user has already responded (unless multiple responses are allowed)
        if (!$this->allow_multiple_responses && $this->hasUserResponded($user)) {
            return false;
        }

        return true;
    }

    public function hasUserResponded(User $user)
    {
        return $this->responses()
                    ->where('user_id', $user->id)
                    ->exists();
    }

    public function getResponseCount()
    {
        return $this->responses()->count();
    }

    public function getUniqueResponseCount()
    {
        return $this->responses()
                    ->distinct('user_id')
                    ->count('user_id');
    }

    public function canBeManagedBy(User $user)
    {
        return $user->isAdminInCompany($this->company) || 
               $user->isManagerInCompany($this->company);
    }

    public function canBeCreatedBy(User $user)
    {
        return $user->isAdminInCompany($this->company) || 
               $user->isManagerInCompany($this->company);
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    public function reopen()
    {
        $this->update(['status' => 'active']);
    }
}