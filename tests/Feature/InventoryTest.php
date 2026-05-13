<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\InventoryLog;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\User;
use App\Services\InventoryService;
use App\Support\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Farmer $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPermissionsAndRoles();

        $this->farmer = Farmer::factory()->create([
            'name' => 'Test Farmer',
            'phone' => '081234567890',
        ]);

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

    public function test_stock_starts_at_zero(): void
    {
        $this->assertEquals(0.0, InventoryService::getCurrentStock());
    }

    public function test_stock_increases_with_transaction(): void
    {
        $this->actingAs($this->admin);

        Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 50.0,
            'price_per_kg' => 100000,
            'total_amount' => 5000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(50.0, InventoryService::getCurrentStock());
    }

    public function test_stock_decreases_with_sale(): void
    {
        $this->actingAs($this->admin);

        Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 100.0,
            'price_per_kg' => 100000,
            'total_amount' => 10000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $sale = Sale::create([
            'sale_type' => 'warehouse',
            'buyer_name' => 'PT. Test',
            'weight_kg' => 40.0,
            'price_per_kg' => 110000,
            'total_amount' => 4400000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(60.0, InventoryService::getCurrentStock());
    }

    public function test_stock_balance_is_accurate(): void
    {
        $this->actingAs($this->admin);

        Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 100.0,
            'price_per_kg' => 100000,
            'total_amount' => 10000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        Sale::create([
            'sale_type' => 'market',
            'buyer_name' => 'Pasar Test',
            'weight_kg' => 30.0,
            'price_per_kg' => 120000,
            'total_amount' => 3600000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        Sale::create([
            'sale_type' => 'retail',
            'buyer_name' => 'Toko Test',
            'weight_kg' => 10.0,
            'price_per_kg' => 130000,
            'total_amount' => 1300000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(60.0, InventoryService::getCurrentStock());
    }

    public function test_inventory_log_has_running_balance(): void
    {
        $this->actingAs($this->admin);

        Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 100.0,
            'price_per_kg' => 100000,
            'total_amount' => 10000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $sale = Sale::create([
            'sale_type' => 'warehouse',
            'buyer_name' => 'PT. Test',
            'weight_kg' => 40.0,
            'price_per_kg' => 110000,
            'total_amount' => 4400000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $saleLog = InventoryLog::where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->first();

        $this->assertNotNull($saleLog);
        $this->assertEquals(60.0, $saleLog->current_stock);
    }

    public function test_low_stock_warning(): void
    {
        $this->assertTrue(InventoryService::isLowStock());

        $this->actingAs($this->admin);
        Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 200.0,
            'price_per_kg' => 100000,
            'total_amount' => 20000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse(InventoryService::isLowStock());
    }
}
