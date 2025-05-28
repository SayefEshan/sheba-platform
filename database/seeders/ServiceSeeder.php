<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ServiceCategory::all()->keyBy('slug');

        $services = [
            // Home Cleaning
            [
                'name' => 'Full House Cleaning',
                'slug' => 'full-house-cleaning',
                'service_category_id' => $categories['home-cleaning']->id,
                'price' => 1500.00,
                'description' => 'Complete house cleaning including all rooms, kitchen, and bathrooms',
                'duration_minutes' => 180,
                'is_active' => true,
            ],
            [
                'name' => 'Kitchen Deep Cleaning',
                'slug' => 'kitchen-deep-cleaning',
                'service_category_id' => $categories['home-cleaning']->id,
                'price' => 800.00,
                'description' => 'Deep cleaning of kitchen including appliances and cabinets',
                'duration_minutes' => 120,
                'is_active' => true,
            ],
            [
                'name' => 'Bathroom Cleaning',
                'slug' => 'bathroom-cleaning',
                'service_category_id' => $categories['home-cleaning']->id,
                'price' => 500.00,
                'description' => 'Thorough bathroom cleaning and sanitization',
                'duration_minutes' => 60,
                'is_active' => true,
            ],

            // AC Repair & Service
            [
                'name' => 'AC General Service',
                'slug' => 'ac-general-service',
                'service_category_id' => $categories['ac-repair-service']->id,
                'price' => 1200.00,
                'description' => 'Complete AC servicing including cleaning and gas check',
                'duration_minutes' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'AC Installation',
                'slug' => 'ac-installation',
                'service_category_id' => $categories['ac-repair-service']->id,
                'price' => 2500.00,
                'description' => 'Professional AC installation service',
                'duration_minutes' => 180,
                'is_active' => true,
            ],

            // Plumbing
            [
                'name' => 'Tap Repair',
                'slug' => 'tap-repair',
                'service_category_id' => $categories['plumbing']->id,
                'price' => 300.00,
                'description' => 'Fix leaking or broken taps',
                'duration_minutes' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'Toilet Repair',
                'slug' => 'toilet-repair',
                'service_category_id' => $categories['plumbing']->id,
                'price' => 600.00,
                'description' => 'Toilet repair and maintenance service',
                'duration_minutes' => 60,
                'is_active' => true,
            ],

            // Electrical
            [
                'name' => 'Wiring Installation',
                'slug' => 'wiring-installation',
                'service_category_id' => $categories['electrical']->id,
                'price' => 1000.00,
                'description' => 'Electrical wiring installation and setup',
                'duration_minutes' => 120,
                'is_active' => true,
            ],
            [
                'name' => 'Switch & Socket Repair',
                'slug' => 'switch-socket-repair',
                'service_category_id' => $categories['electrical']->id,
                'price' => 250.00,
                'description' => 'Repair or replace electrical switches and sockets',
                'duration_minutes' => 30,
                'is_active' => true,
            ],

            // Beauty & Wellness
            [
                'name' => 'Hair Cut & Style',
                'slug' => 'hair-cut-style',
                'service_category_id' => $categories['beauty-wellness']->id,
                'price' => 800.00,
                'description' => 'Professional hair cutting and styling service',
                'duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Facial Treatment',
                'slug' => 'facial-treatment',
                'service_category_id' => $categories['beauty-wellness']->id,
                'price' => 1200.00,
                'description' => 'Relaxing facial treatment and skin care',
                'duration_minutes' => 90,
                'is_active' => true,
            ],

            // Car Care
            [
                'name' => 'Car Wash & Polish',
                'slug' => 'car-wash-polish',
                'service_category_id' => $categories['car-care']->id,
                'price' => 600.00,
                'description' => 'Complete car washing and polishing service',
                'duration_minutes' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Interior Cleaning',
                'slug' => 'interior-cleaning',
                'service_category_id' => $categories['car-care']->id,
                'price' => 400.00,
                'description' => 'Deep cleaning of car interior',
                'duration_minutes' => 60,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            if (!Service::where('slug', $service['slug'])->exists()) {
                Service::create($service);
            }
        }
    }
}
