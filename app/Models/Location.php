<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}