<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMIN,
                'display_name' => 'Admin',
                'description' => 'Can access all admin features except registration pages',
            ],
            [
                'name' => Role::SUPERUSER,
                'display_name' => 'Superuser',
                'description' => 'Can only access registration pages',
            ],
            [
                'name' => Role::USER,
                'display_name' => 'User',
                'description' => 'Basic user role for future use',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
