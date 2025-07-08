<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'options',
        'is_required',
        'order',
        'help_text',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    // Relationships

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class, 'question_id');
    }

    // Scopes

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Methods

    public function getOptionsArrayAttribute()
    {
        return $this->options ?? [];
    }

    public function isMultipleChoice()
    {
        return in_array($this->question_type, ['multiple_choice', 'checkbox']);
    }

    public function isTextBased()
    {
        return in_array($this->question_type, ['text', 'textarea']);
    }

    public function isRating()
    {
        return $this->question_type === 'rating';
    }
}
