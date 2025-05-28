<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::all();

        if ($services->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $customers = [
            ['name' => 'John Doe', 'phone' => '01712345678', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'phone' => '01812345678', 'email' => 'jane@example.com'],
            ['name' => 'Bob Wilson', 'phone' => '01912345678', 'email' => 'bob@example.com'],
        ];

        foreach ($customers as $customer) {
            // Create 2-3 bookings for each customer
            $numBookings = rand(2, 3);

            for ($i = 0; $i < $numBookings; $i++) {
                $service = $services->random();
                $status = $statuses[array_rand($statuses)];
                $scheduledAt = now()->addDays(rand(1, 30));

                Booking::create([
                    'booking_id' => 'BK' . strtoupper(Str::random(8)),
                    'service_id' => $service->id,
                    'customer_name' => $customer['name'],
                    'customer_phone' => $customer['phone'],
                    'customer_email' => $customer['email'],
                    'customer_address' => '123 Sample Street, City',
                    'service_price' => $service->price,
                    'status' => $status,
                    'scheduled_at' => $scheduledAt,
                    'notes' => 'Sample booking notes',
                    'admin_notes' => 'Sample admin notes',
                    'confirmed_at' => $status !== 'pending' ? now() : null,
                    'completed_at' => $status === 'completed' ? now() : null,
                ]);
            }
        }
    }
}
