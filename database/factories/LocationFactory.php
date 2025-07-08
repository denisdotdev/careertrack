<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' ' . fake()->randomElement(['Office', 'Branch', 'HQ', 'Center', 'Facility']),
            'company_id' => Company::factory(),
            'street_address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->country(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'description' => fake()->optional()->paragraph(),
            'is_active' => fake()->boolean(90), // 90% chance of being active
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
        ];
    }

    /**
     * Indicate that the location is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the location is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a location with coordinates.
     */
    public function withCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ]);
    }

    /**
     * Create a headquarters location.
     */
    public function headquarters(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company() . ' Headquarters',
            'description' => 'Main corporate headquarters',
        ]);
    }

    /**
     * Create a branch office location.
     */
    public function branch(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company() . ' Branch Office',
            'description' => 'Regional branch office',
        ]);
    }

    /**
     * Create a remote location.
     */
    public function remote(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company() . ' Remote Office',
            'description' => 'Remote work location',
            'street_address' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
