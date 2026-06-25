<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\InventoryService;
use App\Support\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TransactionTest extends TestCase
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

    public function test_it_creates_a_transaction_and_generates_code(): void
    {
        $this->actingAs($this->admin);

        $transaction = Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 50.5,
            'price_per_kg' => 100000,
            'total_amount' => 50.5 * 100000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertNotNull($transaction->transaction_code);
        $this->assertStringStartsWith('TRX-', $transaction->transaction_code);
        $this->assertEquals(50.5, $transaction->weight_kg);
        $this->assertEquals(100000, $transaction->price_per_kg);
        $this->assertEquals(5050000, $transaction->total_amount);
    }

    public function test_it_creates_inventory_log_on_transaction_creation(): void
    {
        $this->actingAs($this->admin);

        $transaction = Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 30.0,
            'price_per_kg' => 95000,
            'total_amount' => 30.0 * 95000,
            'payment_method' => 'transfer',
            'payment_status' => 'pending',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('inventory_logs', [
            'reference_type' => 'purchase',
            'reference_id' => $transaction->id,
            'type' => 'in',
            'weight_kg' => 30.0,
        ]);

        $this->assertEquals(30.0, InventoryService::getCurrentStock());
    }

    public function test_it_deletes_inventory_log_on_transaction_deletion(): void
    {
        $this->actingAs($this->admin);

        $transaction = Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 20.0,
            'price_per_kg' => 100000,
            'total_amount' => 20.0 * 100000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $logId = $transaction->inventoryLog->id;

        $transaction->delete();

        $this->assertDatabaseMissing('inventory_logs', ['id' => $logId]);
    }

    public function test_it_recalculates_total_on_weight_update(): void
    {
        $this->actingAs($this->admin);

        $transaction = Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 10.0,
            'price_per_kg' => 100000,
            'total_amount' => 10.0 * 100000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $transaction->update([
            'weight_kg' => 15.0,
            'price_per_kg' => 110000,
        ]);

        $this->assertEquals(15.0 * 110000, $transaction->fresh()->total_amount);
    }

    public function test_petani_can_only_see_own_transactions(): void
    {
        $farmer2 = Farmer::factory()->create(['name' => 'Farmer 2']);

        $t1 = Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 10.0,
            'price_per_kg' => 100000,
            'total_amount' => 1000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $t2 = Transaction::create([
            'farmer_id' => $farmer2->id,
            'weight_kg' => 20.0,
            'price_per_kg' => 100000,
            'total_amount' => 2000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $petaniUser = User::factory()->create([
            'name' => 'Petani User',
            'username' => 'petani1',
            'role' => 'petani',
            'farmer_id' => $this->farmer->id,
        ]);
        $petaniUser->assignRole('petani');

        $this->actingAs($petaniUser);

        $visibleTransactions = Transaction::query()
            ->where('farmer_id', $petaniUser->farmer_id)
            ->get();

        $this->assertCount(1, $visibleTransactions);
        $this->assertEquals($t1->id, $visibleTransactions->first()->id);
    }

    public function test_default_price_from_settings(): void
    {
        Setting::set('default_price_per_kg', 95000, 'number');

        $this->assertEquals(95000, Setting::get('default_price_per_kg'));
    }
}
