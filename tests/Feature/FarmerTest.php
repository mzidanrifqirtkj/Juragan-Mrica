<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\User;
use App\Support\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FarmerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPermissionsAndRoles();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'role' => 'admin',
        ]);
        $this->admin->assignRole('admin');
    }

    private function setupPermissionsAndRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RolePermissionMatrix::permissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (RolePermissionMatrix::defaults() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }
    }

    public function test_it_creates_a_farmer_with_auto_code(): void
    {
        $this->actingAs($this->admin);

        $farmer = Farmer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567891',
            'address' => 'Desa Sukamaju',
        ]);

        $this->assertNotNull($farmer->farmer_code);
        $this->assertStringStartsWith('PET', $farmer->farmer_code);
        $this->assertEquals('Budi Santoso', $farmer->name);
    }

    public function test_it_increments_farmer_code(): void
    {
        $this->actingAs($this->admin);

        $farmer1 = Farmer::create(['name' => 'Farmer One', 'phone' => '0811111111']);
        $farmer2 = Farmer::create(['name' => 'Farmer Two', 'phone' => '0811111112']);

        $code1 = (int) substr($farmer1->farmer_code, 3);
        $code2 = (int) substr($farmer2->farmer_code, 3);

        $this->assertEquals($code1 + 1, $code2);
    }

    public function test_farmer_has_transaction_stats(): void
    {
        $this->actingAs($this->admin);

        $farmer = Farmer::create(['name' => 'Test Farmer', 'phone' => '0811111113']);

        $farmer->transactions()->createMany([
            [
                'weight_kg' => 10.0,
                'price_per_kg' => 100000,
                'total_amount' => 1000000,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'transaction_date' => now(),
                'created_by' => $this->admin->id,
            ],
            [
                'weight_kg' => 20.0,
                'price_per_kg' => 100000,
                'total_amount' => 2000000,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'transaction_date' => now(),
                'created_by' => $this->admin->id,
            ],
        ]);

        $this->assertEquals(30.0, $farmer->total_weight);
        $this->assertEquals(3000000, $farmer->total_amount);
        $this->assertEquals(2, $farmer->transaction_count);
        $this->assertEquals(15.0, $farmer->average_weight);
    }

    public function test_farmer_scope_active(): void
    {
        $this->actingAs($this->admin);

        Farmer::create(['name' => 'Active Farmer', 'phone' => '0811111114', 'is_active' => true]);
        Farmer::create(['name' => 'Inactive Farmer', 'phone' => '0811111115', 'is_active' => false]);

        $activeCount = Farmer::active()->count();

        $this->assertEquals(1, $activeCount);
    }
}
