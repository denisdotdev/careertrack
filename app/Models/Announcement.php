<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'company_id',
        'status',
        'priority',
        'published_at',
    ];

    // Relationships

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}