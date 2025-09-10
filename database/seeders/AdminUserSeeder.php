<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $superuserRole = Role::where('name', Role::SUPERUSER)->first();

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@coinvo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => $adminRole?->id,
                'email_verified_at' => now(),
            ]
        );

        // Create superuser
        User::firstOrCreate(
            ['email' => 'superuser@coinvo.com'],
            [
                'name' => 'Super User',
                'password' => Hash::make('password'),
                'role_id' => $superuserRole?->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
