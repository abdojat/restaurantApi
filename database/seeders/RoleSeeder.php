<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access and control',
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Can manage users, tables, reservations, and menu',
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'Can manage orders and update order statuses',
            ],
            [
                'name' => 'customer',
                'display_name' => 'Customer',
                'description' => 'Can make reservations and place orders',
            ],
            [
                'name' => 'quality manager',
                'display_name' => 'Quality Manager',
                'description' => 'Ensures products or services consistently meet established standards and customer expectations',
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
