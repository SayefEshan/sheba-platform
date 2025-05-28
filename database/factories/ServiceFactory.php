<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'service_category_id' => ServiceCategory::factory(),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'description' => $this->faker->paragraph(),
            'duration_minutes' => $this->faker->randomElement([30, 60, 90, 120, 180, 240]),
            'is_active' => true,
            'images' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
