<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'booking_id' => 'SB' . strtoupper(Str::random(8)),
            'service_id' => Service::factory(),
            'customer_name' => $this->faker->name,
            'customer_phone' => '01' . $this->faker->numerify('########'),
            'customer_email' => $this->faker->optional()->email,
            'customer_address' => $this->faker->optional()->address,
            'service_price' => $this->faker->randomFloat(2, 100, 5000),
            'status' => 'pending',
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'notes' => $this->faker->optional()->sentence,
            'admin_notes' => null,
            'confirmed_at' => null,
            'completed_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
            'confirmed_at' => now()->subHours(2),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
