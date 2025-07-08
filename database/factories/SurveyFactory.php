<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Survey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'company_id' => Company::factory(),
            'created_by' => User::factory(),
            'status' => fake()->randomElement(['draft', 'active', 'closed']),
            'start_date' => fake()->optional()->dateTimeBetween('now', '+1 week'),
            'end_date' => fake()->optional()->dateTimeBetween('+1 week', '+1 month'),
            'is_anonymous' => fake()->boolean(20), // 20% chance of being anonymous
            'allow_multiple_responses' => fake()->boolean(30), // 30% chance of allowing multiple responses
        ];
    }

    /**
     * Indicate that the survey is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addWeeks(2),
        ]);
    }

    /**
     * Indicate that the survey is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    /**
     * Indicate that the survey is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'end_date' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the survey is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => true,
        ]);
    }

    /**
     * Indicate that the survey allows multiple responses.
     */
    public function allowMultipleResponses(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_multiple_responses' => true,
        ]);
    }
}