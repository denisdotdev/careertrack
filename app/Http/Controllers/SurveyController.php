<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Traits\HasCompanyRoles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    use HasCompanyRoles;

    /**
     * Display a listing of surveys for a company.
     */
    public function index(Company $company)
    {
        $user = auth()->user();
        
        // Check if user is a member of the company
        if (!$company->hasUser($user)) {
            abort(403, 'You are not a member of this company');
        }

        $surveys = $company->surveys()
                          ->with(['creator', 'questions'])
                          ->latest()
                          ->get();

        return response()->json([
            'surveys' => $surveys,
            'user_role' => $user->getRoleInCompany($company),
            'can_create_surveys' => $this->canPerformAction($company, 'create_survey'),
        ]);
    }

    /**
     * Store a newly created survey.
     */
    public function store(Request $request, Company $company)
    {
        // Only admins and managers can create surveys
        $this->authorizeAdminOrManagerInCompany($company);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'is_anonymous' => 'boolean',
            'allow_multiple_responses' => 'boolean',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.question_type' => ['required', Rule::in([
                'text', 'textarea', 'multiple_choice', 'checkbox', 'rating', 'date', 'email'
            ])],
            'questions.*.options' => 'nullable|array',
            'questions.*.is_required' => 'boolean',
            'questions.*.help_text' => 'nullable|string',
        ]);

        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'company_id' => $company->id,
            'created_by' => auth()->id(),
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_anonymous' => $request->is_anonymous ?? false,
            'allow_multiple_responses' => $request->allow_multiple_responses ?? false,
        ]);

        // Create questions
        foreach ($request->questions as $index => $questionData) {
            SurveyQuestion::create([
                'survey_id' => $survey->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'] ?? null,
                'is_required' => $questionData['is_required'] ?? false,
                'order' => $index + 1,
                'help_text' => $questionData['help_text'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Survey created successfully',
            'survey' => $survey->load('questions'),
        ], 201);
    }

    /**
     * Display the specified survey.
     */
    public function show(Survey $survey)
    {
        $user = auth()->user();
        
        // Check if user is a member of the company
        if (!$survey->company->hasUser($user)) {
            abort(403, 'You are not a member of this company');
        }

        $survey->load(['questions', 'creator', 'company']);

        return response()->json([
            'survey' => $survey,
            'can_fill' => $survey->canBeFilledBy($user),
            'has_responded' => $survey->hasUserResponded($user),
            'can_manage' => $survey->canBeManagedBy($user),
        ]);
    }

    /**
     * Update the specified survey.
     */
    public function update(Request $request, Survey $survey)
    {
        // Only admins and managers can update surveys
        if (!$survey->canBeManagedBy(auth()->user())) {
            abort(403, 'You do not have permission to update this survey');
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', 'required', Rule::in(['draft', 'active', 'closed'])],
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'is_anonymous' => 'boolean',
            'allow_multiple_responses' => 'boolean',
        ]);

        $survey->update($request->only([
            'title', 'description', 'status', 'start_date', 'end_date',
            'is_anonymous', 'allow_multiple_responses'
        ]));

        return response()->json([
            'message' => 'Survey updated successfully',
            'survey' => $survey->fresh()->load('questions'),
        ]);
    }

    /**
     * Remove the specified survey.
     */
    public function destroy(Survey $survey)
    {
        // Only admins and managers can delete surveys
        if (!$survey->canBeManagedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this survey');
        }

        $survey->delete();

        return response()->json([
            'message' => 'Survey deleted successfully',
        ]);
    }

    /**
     * Submit responses to a survey.
     */
    public function submitResponses(Request $request, Survey $survey)
    {
        $user = auth()->user();

        // Check if user can fill the survey
        if (!$survey->canBeFilledBy($user)) {
            abort(403, 'You cannot fill this survey');
        }

        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:survey_questions,id',
            'responses.*.answer' => 'required|string',
        ]);

        // Check if user has already responded (unless multiple responses are allowed)
        if (!$survey->allow_multiple_responses && $survey->hasUserResponded($user)) {
            abort(400, 'You have already responded to this survey');
        }

        $responses = [];
        foreach ($request->responses as $responseData) {
            $question = SurveyQuestion::find($responseData['question_id']);
            
            // Validate that the question belongs to this survey
            if ($question->survey_id !== $survey->id) {
                abort(400, 'Invalid question for this survey');
            }

            // Validate required questions
            if ($question->is_required && empty($responseData['answer'])) {
                abort(400, "Question '{$question->question_text}' is required");
            }

            $responses[] = SurveyResponse::create([
                'survey_id' => $survey->id,
                'user_id' => $survey->is_anonymous ? null : $user->id,
                'question_id' => $question->id,
                'answer' => $responseData['answer'],
                'submitted_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Survey responses submitted successfully',
            'responses_count' => count($responses),
        ], 201);
    }

    /**
     * Get survey results/analytics.
     */
    public function results(Survey $survey)
    {
        // Only admins and managers can view results
        if (!$survey->canBeManagedBy(auth()->user())) {
            abort(403, 'You do not have permission to view survey results');
        }

        $questions = $survey->questions()->with('responses')->get();
        
        $results = $questions->map(function ($question) {
            $responses = $question->responses;
            
            $analysis = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'total_responses' => $responses->count(),
                'analysis' => $this->analyzeResponses($question, $responses),
            ];

            return $analysis;
        });

        return response()->json([
            'survey' => $survey,
            'total_responses' => $survey->getResponseCount(),
            'unique_responses' => $survey->getUniqueResponseCount(),
            'results' => $results,
        ]);
    }

    /**
     * Analyze responses for a specific question.
     */
    private function analyzeResponses($question, $responses)
    {
        if ($responses->isEmpty()) {
            return ['message' => 'No responses yet'];
        }

        return match ($question->question_type) {
            'multiple_choice', 'checkbox' => $this->analyzeChoiceResponses($question, $responses),
            'rating' => $this->analyzeRatingResponses($responses),
            'text', 'textarea', 'email' => $this->analyzeTextResponses($responses),
            default => ['message' => 'Analysis not available for this question type'],
        };
    }

    /**
     * Analyze multiple choice and checkbox responses.
     */
    private function analyzeChoiceResponses($question, $responses)
    {
        $options = $question->options ?? [];
        $counts = array_fill_keys($options, 0);
        
        foreach ($responses as $response) {
            if ($question->question_type === 'checkbox') {
                // For checkboxes, answers are comma-separated
                $selectedOptions = explode(',', $response->answer);
                foreach ($selectedOptions as $option) {
                    $option = trim($option);
                    if (in_array($option, $options)) {
                        $counts[$option]++;
                    }
                }
            } else {
                // For multiple choice, single answer
                if (in_array($response->answer, $options)) {
                    $counts[$response->answer]++;
                }
            }
        }

        return [
            'type' => 'choice_analysis',
            'options' => $options,
            'counts' => $counts,
            'percentages' => array_map(function ($count) use ($responses) {
                return $responses->count() > 0 ? round(($count / $responses->count()) * 100, 2) : 0;
            }, $counts),
        ];
    }

    /**
     * Analyze rating responses.
     */
    private function analyzeRatingResponses($responses)
    {
        $ratings = $responses->pluck('answer')->map('intval');
        
        return [
            'type' => 'rating_analysis',
            'average' => round($ratings->avg(), 2),
            'min' => $ratings->min(),
            'max' => $ratings->max(),
            'distribution' => $ratings->countBy()->toArray(),
        ];
    }

    /**
     * Analyze text responses.
     */
    private function analyzeTextResponses($responses)
    {
        return [
            'type' => 'text_analysis',
            'total_responses' => $responses->count(),
            'sample_responses' => $responses->take(5)->pluck('answer')->toArray(),
        ];
    }

    /**
     * Activate a survey.
     */
    public function activate(Survey $survey)
    {
        if (!$survey->canBeManagedBy(auth()->user())) {
            abort(403, 'You do not have permission to activate this survey');
        }

        $survey->activate();

        return response()->json([
            'message' => 'Survey activated successfully',
            'survey' => $survey->fresh(),
        ]);
    }

    /**
     * Close a survey.
     */
    public function close(Survey $survey)
    {
        if (!$survey->canBeManagedBy(auth()->user())) {
            abort(403, 'You do not have permission to close this survey');
        }

        $survey->close();

        return response()->json([
            'message' => 'Survey closed successfully',
            'survey' => $survey->fresh(),
        ]);
    }
}
