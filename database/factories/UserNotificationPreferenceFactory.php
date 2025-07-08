<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotificationPreference>
 */
class UserNotificationPreferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserNotificationPreference::class;

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
            'notification_type' => fake()->randomElement(['survey_available', 'announcement', 'location_assignment', 'goal_update']),
            'email_enabled' => fake()->boolean(80), // 80% chance of being enabled
            'in_app_enabled' => fake()->boolean(90), // 90% chance of being enabled
            'push_enabled' => fake()->boolean(30), // 30% chance of being enabled
        ];
    }

    /**
     * Create preferences for survey notifications.
     */
    public function surveyAvailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'survey_available',
            'email_enabled' => true,
            'in_app_enabled' => true,
            'push_enabled' => fake()->boolean(40),
        ]);
    }

    /**
     * Create preferences for announcement notifications.
     */
    public function announcement(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'announcement',
            'email_enabled' => fake()->boolean(70),
            'in_app_enabled' => true,
            'push_enabled' => fake()->boolean(20),
        ]);
    }

    /**
     * Create preferences for location assignment notifications.
     */
    public function locationAssignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'location_assignment',
            'email_enabled' => true,
            'in_app_enabled' => true,
            'push_enabled' => fake()->boolean(50),
        ]);
    }

    /**
     * Create preferences for goal update notifications.
     */
    public function goalUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'goal_update',
            'email_enabled' => fake()->boolean(60),
            'in_app_enabled' => true,
            'push_enabled' => fake()->boolean(25),
        ]);
    }

    /**
     * Create preferences with all notifications enabled.
     */
    public function allEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => true,
            'in_app_enabled' => true,
            'push_enabled' => true,
        ]);
    }

    /**
     * Create preferences with all notifications disabled.
     */
    public function allDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => false,
            'in_app_enabled' => false,
            'push_enabled' => false,
        ]);
    }

    /**
     * Create preferences with only in-app notifications enabled.
     */
    public function inAppOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => false,
            'in_app_enabled' => true,
            'push_enabled' => false,
        ]);
    }

    /**
     * Create preferences with only email notifications enabled.
     */
    public function emailOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => true,
            'in_app_enabled' => false,
            'push_enabled' => false,
        ]);
    }

    /**
     * Create preferences with email and in-app enabled (most common).
     */
    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_enabled' => true,
            'in_app_enabled' => true,
            'push_enabled' => false,
        ]);
    }
}
