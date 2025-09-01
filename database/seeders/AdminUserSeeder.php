<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@restaurant.com',
            'phone_number' => '+1234567890',
            'password' => Hash::make('admin123'),
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole->id);

        // Create manager user
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@restaurant.com',
            'phone_number' => '+1234567891',
            'password' => Hash::make('manager123'),
            'role_id' => Role::where('name', 'manager')->first()->id,
        ]);

        // Assign manager role
        $managerRole = Role::where('name', 'manager')->first();
        $manager->roles()->attach($managerRole->id);

        // Create cashier user
        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@restaurant.com',
            'phone_number' => '+1234567892',
            'password' => Hash::make('cashier123'),
            'role_id' => Role::where('name', 'cashier')->first()->id,
        ]);

        // Assign cashier role
        $cashierRole = Role::where('name', 'cashier')->first();
        $cashier->roles()->attach($cashierRole->id);

        // Create sample customer
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@restaurant.com',
            'phone_number' => '+1234567893',
            'password' => Hash::make('customer123'),
            'role_id' => Role::where('name', 'customer')->first()->id,
        ]);

        // Assign customer role
        $customerRole = Role::where('name', 'customer')->first();
        $customer->roles()->attach($customerRole->id);
    }
}
