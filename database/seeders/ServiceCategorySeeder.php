<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Home Cleaning',
                'slug' => 'home-cleaning',
                'description' => 'Professional home cleaning services for your house',
                'is_active' => true,
            ],
            [
                'name' => 'AC Repair & Service',
                'slug' => 'ac-repair-service',
                'description' => 'Air conditioning repair and maintenance services',
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing',
                'slug' => 'plumbing',
                'description' => 'Professional plumbing services and repairs',
                'is_active' => true,
            ],
            [
                'name' => 'Electrical',
                'slug' => 'electrical',
                'description' => 'Electrical installation and repair services',
                'is_active' => true,
            ],
            [
                'name' => 'Beauty & Wellness',
                'slug' => 'beauty-wellness',
                'description' => 'Beauty and wellness services at your doorstep',
                'is_active' => true,
            ],
            [
                'name' => 'Car Care',
                'slug' => 'car-care',
                'description' => 'Professional car washing and maintenance services',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
