<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SurveyQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questionType = fake()->randomElement([
            'text',
            'textarea',
            'multiple_choice',
            'checkbox',
            'rating',
            'date',
            'email'
        ]);

        return [
            'survey_id' => Survey::factory(),
            'question_text' => fake()->sentence() . '?',
            'question_type' => $questionType,
            'options' => $this->getOptionsForType($questionType),
            'is_required' => fake()->boolean(70), // 70% chance of being required
            'order' => fake()->numberBetween(1, 10),
            'help_text' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Get options for different question types.
     */
    private function getOptionsForType(string $type): ?array
    {
        return match ($type) {
            'multiple_choice' => [
                fake()->word(),
                fake()->word(),
                fake()->word(),
                fake()->word(),
            ],
            'checkbox' => [
                fake()->word(),
                fake()->word(),
                fake()->word(),
                fake()->word(),
                fake()->word(),
            ],
            default => null,
        };
    }

    /**
     * Indicate that the question is required.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    /**
     * Indicate that the question is optional.
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }

    /**
     * Create a text question.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'text',
            'options' => null,
        ]);
    }

    /**
     * Create a textarea question.
     */
    public function textarea(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'textarea',
            'options' => null,
        ]);
    }

    /**
     * Create a multiple choice question.
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'multiple_choice',
            'options' => [
                'Option A',
                'Option B',
                'Option C',
                'Option D',
            ],
        ]);
    }

    /**
     * Create a checkbox question.
     */
    public function checkbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'checkbox',
            'options' => [
                'Checkbox 1',
                'Checkbox 2',
                'Checkbox 3',
                'Checkbox 4',
            ],
        ]);
    }

    /**
     * Create a rating question.
     */
    public function rating(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'rating',
            'options' => null,
        ]);
    }

    /**
     * Create a date question.
     */
    public function date(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'date',
            'options' => null,
        ]);
    }

    /**
     * Create an email question.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'email',
            'options' => null,
        ]);
    }
}
