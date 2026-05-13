<?php

namespace Tests\Feature;

use App\Models\Farmer;
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

class SaleTest extends TestCase
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

    private function createTransactionWithStock(float $weight = 100.0): Transaction
    {
        $this->actingAs($this->admin);

        return Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => $weight,
            'price_per_kg' => 100000,
            'total_amount' => $weight * 100000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_it_creates_a_sale_and_generates_code(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->createTransactionWithStock(50.0);

        $sale = Sale::create([
            'sale_type' => 'warehouse',
            'transaction_id' => $transaction->id,
            'buyer_name' => 'CV. Test Buyer',
            'buyer_phone' => '081999888777',
            'weight_kg' => 30.0,
            'price_per_kg' => 110000,
            'total_amount' => 30.0 * 110000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertNotNull($sale->sale_code);
        $this->assertStringStartsWith('SALE-', $sale->sale_code);
        $this->assertEquals(30.0, $sale->weight_kg);
        $this->assertEquals(110000, $sale->price_per_kg);
        $this->assertEquals(3300000, $sale->total_amount);
    }

    public function test_it_marks_transaction_as_sold_when_sale_created(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->createTransactionWithStock(50.0);

        $this->assertFalse($transaction->fresh()->is_sold);

        Sale::create([
            'sale_type' => 'market',
            'transaction_id' => $transaction->id,
            'buyer_name' => 'Pasar Test',
            'weight_kg' => 20.0,
            'price_per_kg' => 120000,
            'total_amount' => 20.0 * 120000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($transaction->fresh()->is_sold);
    }

    public function test_it_reverts_is_sold_when_sale_deleted(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->createTransactionWithStock(50.0);

        $sale = Sale::create([
            'sale_type' => 'retail',
            'transaction_id' => $transaction->id,
            'buyer_name' => 'Toko Test',
            'weight_kg' => 10.0,
            'price_per_kg' => 130000,
            'total_amount' => 10.0 * 130000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($transaction->fresh()->is_sold);

        $sale->delete();

        $this->assertFalse($transaction->fresh()->is_sold);
    }

    public function test_it_creates_inventory_log_on_sale(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->createTransactionWithStock(100.0);

        $initialStock = InventoryService::getCurrentStock();

        $sale = Sale::create([
            'sale_type' => 'warehouse',
            'transaction_id' => $transaction->id,
            'buyer_name' => 'PT. Test',
            'weight_kg' => 40.0,
            'price_per_kg' => 110000,
            'total_amount' => 40.0 * 110000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('inventory_logs', [
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'type' => 'out',
            'weight_kg' => 40.0,
        ]);

        $this->assertEquals($initialStock - 40.0, InventoryService::getCurrentStock());
    }

    public function test_it_recalculates_inventory_on_weight_change(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->createTransactionWithStock(100.0);

        $sale = Sale::create([
            'sale_type' => 'warehouse',
            'transaction_id' => $transaction->id,
            'buyer_name' => 'PT. Test',
            'weight_kg' => 30.0,
            'price_per_kg' => 110000,
            'total_amount' => 30.0 * 110000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $stockAfterCreate = InventoryService::getCurrentStock();

        $sale->update(['weight_kg' => 20.0]);

        $this->assertEquals($stockAfterCreate + 10.0, InventoryService::getCurrentStock());
    }

    public function test_it_prevents_sale_when_stock_insufficient(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->createTransactionWithStock(10.0);

        $this->expectException(\Exception::class);

        Sale::create([
            'sale_type' => 'warehouse',
            'transaction_id' => $transaction->id,
            'buyer_name' => 'PT. Test',
            'weight_kg' => 100.0,
            'price_per_kg' => 110000,
            'total_amount' => 100.0 * 110000,
            'sale_date' => now(),
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_sale_types_are_valid(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->createTransactionWithStock(50.0);

        $validTypes = ['warehouse', 'market', 'retail'];

        foreach ($validTypes as $type) {
            $sale = Sale::create([
                'sale_type' => $type,
                'transaction_id' => $transaction->id,
                'buyer_name' => "Buyer {$type}",
                'weight_kg' => 5.0,
                'price_per_kg' => 100000,
                'total_amount' => 5.0 * 100000,
                'sale_date' => now(),
                'created_by' => $this->admin->id,
            ]);

            $this->assertEquals($type, $sale->sale_type);
        }
    }
}
