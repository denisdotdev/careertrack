<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(3),
            'company_id' => Company::factory(),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}