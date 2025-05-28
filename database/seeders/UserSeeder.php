<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admins
        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@sheba.xyz',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Regular Admin',
                'email' => 'manager@sheba.xyz',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        }

        // Create regular users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('user123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('user123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // Create additional random users
        User::factory(5)->create();
    }
}
