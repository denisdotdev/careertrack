<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'title',
        'description',
        'target_date',
        'status',
        'priority',
    ];
}