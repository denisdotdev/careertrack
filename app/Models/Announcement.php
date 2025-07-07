<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content'
    ];

    // Relationships

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}