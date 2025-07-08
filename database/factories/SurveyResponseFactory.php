<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyResponse>
 */
class SurveyResponseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SurveyResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $question = SurveyQuestion::factory()->create();
        
        return [
            'survey_id' => $question->survey_id,
            'user_id' => User::factory(),
            'question_id' => $question->id,
            'answer' => $this->generateAnswer($question),
            'submitted_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Generate an appropriate answer based on question type.
     */
    private function generateAnswer(SurveyQuestion $question): string
    {
        return match ($question->question_type) {
            'text' => fake()->sentence(),
            'textarea' => fake()->paragraph(),
            'multiple_choice' => fake()->randomElement($question->options ?? ['Option A']),
            'checkbox' => implode(',', fake()->randomElements($question->options ?? ['Checkbox 1'], fake()->numberBetween(1, 3))),
            'rating' => (string) fake()->numberBetween(1, 5),
            'date' => fake()->date(),
            'email' => fake()->email(),
            default => fake()->sentence(),
        };
    }

    /**
     * Create an anonymous response.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Create a response for a specific question type.
     */
    public function forQuestionType(string $type): static
    {
        $question = SurveyQuestion::factory()->create(['question_type' => $type]);
        
        return $this->state(fn (array $attributes) => [
            'survey_id' => $question->survey_id,
            'question_id' => $question->id,
            'answer' => $this->generateAnswer($question),
        ]);
    }

    /**
     * Create a text response.
     */
    public function text(): static
    {
        return $this->forQuestionType('text');
    }

    /**
     * Create a textarea response.
     */
    public function textarea(): static
    {
        return $this->forQuestionType('textarea');
    }

    /**
     * Create a multiple choice response.
     */
    public function multipleChoice(): static
    {
        return $this->forQuestionType('multiple_choice');
    }

    /**
     * Create a checkbox response.
     */
    public function checkbox(): static
    {
        return $this->forQuestionType('checkbox');
    }

    /**
     * Create a rating response.
     */
    public function rating(): static
    {
        return $this->forQuestionType('rating');
    }

    /**
     * Create a date response.
     */
    public function date(): static
    {
        return $this->forQuestionType('date');
    }

    /**
     * Create an email response.
     */
    public function email(): static
    {
        return $this->forQuestionType('email');
    }
}
