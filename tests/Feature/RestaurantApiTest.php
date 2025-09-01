<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Table;
use App\Models\Category;
use App\Models\Dish;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RestaurantApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $cashier;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        // Create users
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);
        $this->admin->roles()->attach(Role::where('name', 'admin')->first()->id);

        $this->manager = User::factory()->create([
            'email' => 'manager@test.com',
            'role_id' => Role::where('name', 'manager')->first()->id,
        ]);
        $this->manager->roles()->attach(Role::where('name', 'manager')->first()->id);

        $this->cashier = User::factory()->create([
            'email' => 'cashier@test.com',
            'role_id' => Role::where('name', 'cashier')->first()->id,
        ]);
        $this->cashier->roles()->attach(Role::where('name', 'cashier')->first()->id);

        $this->customer = User::factory()->create([
            'email' => 'customer@test.com',
            'role_id' => Role::where('name', 'customer')->first()->id,
        ]);
        $this->customer->roles()->attach(Role::where('name', 'customer')->first()->id);
    }

    public function test_public_menu_endpoints_are_accessible()
    {
        $response = $this->get('/api/menu');
        $response->assertStatus(200);

        $response = $this->get('/api/menu/categories');
        $response->assertStatus(200);
    }

    public function test_user_registration_creates_customer()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);



        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role_id' => Role::where('name', 'customer')->first()->id,
        ]);
    }

    public function test_admin_can_access_admin_endpoints()
    {
        $response = $this->actingAs($this->admin)
            ->get('/api/admin/users');
        
        $response->assertStatus(200);
    }

    public function test_manager_can_access_manager_endpoints()
    {
        $response = $this->actingAs($this->manager)
            ->get('/api/manager/tables');
        
        $response->assertStatus(200);
    }

    public function test_cashier_can_access_cashier_endpoints()
    {
        $response = $this->actingAs($this->cashier)
            ->get('/api/cashier/orders');
        
        $response->assertStatus(200);
    }

    public function test_customer_can_access_customer_endpoints()
    {
        $response = $this->actingAs($this->customer)
            ->get('/api/customer/reservations');
        
        $response->assertStatus(200);
    }

    public function test_unauthorized_access_is_denied()
    {
        $response = $this->actingAs($this->customer)
            ->get('/api/admin/users');
        
        $response->assertStatus(403);
    }

    public function test_manager_can_create_table()
    {
        $response = $this->actingAs($this->manager)
            ->post('/api/manager/tables', [
                'name' => 'Table 1',
                'capacity' => 4,
                'description' => 'Window table',
                'is_active' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tables', [
            'name' => 'Table 1',
            'capacity' => 4,
        ]);
    }

    public function test_manager_can_create_category()
    {
        $response = $this->actingAs($this->manager)
            ->post('/api/manager/categories', [
                'name' => 'Main Course',
                'description' => 'Main dishes',
                'is_active' => true,
                'sort_order' => 1,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'name' => 'Main Course',
        ]);
    }

    public function test_manager_can_create_dish()
    {
        $category = Category::create([
            'name' => 'Main Course',
            'slug' => 'main-course',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->manager)
            ->post('/api/manager/dishes', [
                'name' => 'Grilled Salmon',
                'description' => 'Fresh grilled salmon with herbs',
                'price' => 25.99,
                'category_id' => $category->id,
                'is_vegetarian' => false,
                'is_vegan' => false,
                'is_gluten_free' => true,
                'is_available' => true,
                'preparation_time' => 20,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dishes', [
            'name' => 'Grilled Salmon',
            'price' => 25.99,
        ]);
    }

    public function test_customer_can_create_reservation()
    {
        $table = Table::create([
            'name' => 'Table 1',
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->post('/api/customer/reservations', [
                'table_id' => $table->id,
                'reservation_date' => now()->addDay()->setTime(19, 0),
                'guests' => 3,
                'special_requests' => 'Window seat preferred',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->customer->id,
            'table_id' => $table->id,
            'guests' => 3,
        ]);
    }

    public function test_customer_can_create_order()
    {
        $category = Category::create([
            'name' => 'Main Course',
            'slug' => 'main-course',
            'is_active' => true,
        ]);

        $dish = Dish::create([
            'name' => 'Grilled Salmon',
            'description' => 'Fresh grilled salmon with herbs',
            'price' => 25.99,
            'category_id' => $category->id,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->post('/api/customer/orders', [
                'type' => 'dine_in',
                'items' => [
                    [
                        'dish_id' => $dish->id,
                        'quantity' => 2,
                        'special_instructions' => 'Medium rare',
                    ],
                ],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'type' => 'dine_in',
        ]);
    }
}
