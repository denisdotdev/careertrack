<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'type' => fake()->randomElement(['survey_available', 'announcement', 'location_assignment', 'goal_update']),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'data' => [
                'company_name' => fake()->company(),
            ],
            'status' => fake()->randomElement(['unread', 'read', 'dismissed']),
            'read_at' => fake()->optional()->dateTime(),
            'dismissed_at' => fake()->optional()->dateTime(),
        ];
    }

    /**
     * Create a survey available notification.
     */
    public function surveyAvailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'survey_available',
            'title' => 'New Survey Available',
            'message' => fake()->sentence(10, false, 'A new survey is now available for you to complete.'),
            'data' => [
                'survey_id' => fake()->numberBetween(1, 100),
                'survey_title' => fake()->sentence(3),
                'company_name' => fake()->company(),
            ],
        ]);
    }

    /**
     * Create an announcement notification.
     */
    public function announcement(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'announcement',
            'title' => 'New Announcement',
            'message' => fake()->sentence(8, false, 'New announcement: ') . fake()->sentence(),
            'data' => [
                'announcement_id' => fake()->numberBetween(1, 100),
                'announcement_title' => fake()->sentence(3),
                'company_name' => fake()->company(),
            ],
        ]);
    }

    /**
     * Create a location assignment notification.
     */
    public function locationAssignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'location_assignment',
            'title' => 'Location Assignment',
            'message' => fake()->sentence(8, false, 'You have been assigned to ') . fake()->company() . ' location.',
            'data' => [
                'location_id' => fake()->numberBetween(1, 50),
                'location_name' => fake()->company() . ' ' . fake()->randomElement(['Office', 'Branch', 'HQ']),
                'company_name' => fake()->company(),
            ],
        ]);
    }

    /**
     * Create a goal update notification.
     */
    public function goalUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'goal_update',
            'title' => 'Goal Update',
            'message' => fake()->sentence(8, false, 'Goal has been updated: ') . fake()->sentence(),
            'data' => [
                'goal_id' => fake()->numberBetween(1, 50),
                'goal_title' => fake()->sentence(3),
                'company_name' => fake()->company(),
            ],
        ]);
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unread',
            'read_at' => null,
            'dismissed_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'read',
            'read_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'dismissed_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is dismissed.
     */
    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dismissed',
            'read_at' => null,
            'dismissed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create a recent notification (within last 7 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'updated_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create an old notification (older than 30 days).
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-90 days', '-30 days'),
            'updated_at' => fake()->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }
}
