<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'user_id',
        'question_id',
        'answer',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // Relationships

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
    }

    // Scopes

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeBySurvey($query, Survey $survey)
    {
        return $query->where('survey_id', $survey->id);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('submitted_at', 'desc');
    }

    // Methods

    public function isAnonymous()
    {
        return is_null($this->user_id);
    }

    public function getFormattedAnswerAttribute()
    {
        if ($this->question->isMultipleChoice()) {
            return $this->answer;
        }

        if ($this->question->isRating()) {
            return $this->answer . '/5';
        }

        return $this->answer;
    }
}
