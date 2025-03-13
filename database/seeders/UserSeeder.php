<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // Create a super-admin user
        User::create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super-admin',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'name' => 'Tush',
            'email' => 'xyz@gmail.com',
            'password' => Hash::make('11111111'),
            'role' => 'user',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'name' => 'Tahsan Tanjim',
            'email' => 'tahsan@gmail.com',
            'password' => Hash::make('11111111'),
            'role' => 'user',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // Create some regular users
        User::factory()->count(10)->create()->each(function ($user) {
            $user->email_verified_at = now()->subDays(rand(1, 365));
            $user->save();
        });
    }
}
